# StickMarket service

## Information

Requirements:
- docker
- docker-compose
- free `80` and `443` ports (or change it from docker-compose .yml)

## Up

```
docker load < stick-market.tar
docker-compose up --build -d
```

## Notes

This service works correctly only with php8.1.0-dev version.
The code inside the image (`stick-market.tar`) contains an error in the UpdateBonus function (line 115, need to rename `:uuid` to `:username` param).