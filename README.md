# 2023-service-sibirctf-stickmarket

Онлайн-маркет палок истины: загрузка zip со stick.yml и jpg, покупка и просмотр скрытой фразы.

- **Игра:** SibirCTF 2023 (2023)
- **Автор:** —
- **Стек:** PHP, Nginx, SQLite
- **Порт сервиса:** 80

## Запуск

Подробности — в [`vuln-service/README.md`](vuln-service/README.md). Типовой запуск:

```
cd vuln-service
docker compose up --build -d
```

## Структура репозитория

- `vuln-service/` — уязвимый сервис;
- `checker_sibirctf_stickmarket/` — чекер для жюри ctf01d;
- `README.md` — этот файл.

## Уязвимости

- **Path Traversal** (High) — Nginx alias без завершающего / позволяет читать файлы вне директории, включая market.db.
- **Backdoor** (Critical) — PHP 8.1.0-dev бекдор позволяет выполнять команды через User-Agentt.
- **Arbitrary Function Call** (High) — call_user_func_array вызывает функции по параметру check, включая UpdateBonus и deleteDirectory.
