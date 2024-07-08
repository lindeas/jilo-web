# Jilo Web

## TODO

- ~~jilo-web.db outside web root~~

- ~~jilo-web.db writable by web server user~~

- major refactoring after v0.1

- - add bootstrap template

- - clean up the code to follow model-view--controller style

- - no HTML inside PHP code

- - put all additional functions in files in a separate folder

- - reduce try/catch usage, use it only for critical errors

- - move all SQL code in the model classes, one query per method

- - add 'limit' to SQL and make pagination

- - pretty URLs routing (no htaccess, it needs to be in PHP code so that it can be used with all servers)

- add mysql/mariadb option
