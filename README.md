## TODO: Features
- [X] Global logging helper
- [ ] Internal Cache Slave (needed??)
- [X] AutWorker and AuthSlave
- [X] Allow for shared authorization between sites
- [X] Escalable router, allowing for hundreds of endpoints without performance drop
- [X] Architecture is getting slower in local. Improve optimization. (It was my container...)
- [ ] Default error behaviour Worker
- [ ] Test php-error-insight
- [X] ProcessApi && ProcessView kernel methods are getting big... 
- [ ] Improve exception usage and customization of messages
- [X] DataSlave and DataWorker
- [ ] Improve URI parsing with PHP 8.5
- [ ] Abstract shared authorization between sites

## TODO: Bugs
- [X] Exception throws message to client in response.
- [X] 404 returns 500 instead?
- [X] .env loading fails