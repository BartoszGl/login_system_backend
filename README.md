# LoginSystemBackend

W celu uruchomienia wykonać:

composer install

symfony server:start --no-tls

php bin/console lexik:jwt:generate-keypair

System został napisany z użyciem stacku Symfony + Mysql.
Zaimplementowano autentykację za pomocą JWT, wykorzystana biblioteka to https://github.com/lexik/LexikJWTAuthenticationBundle

Konfiguracja security znajduje się w config/services.yaml oraz config/packages/security.yaml

Zaimplementowano system wysyłania maili do użytkownika. Mail jest wysyłany z konta google. Na "prawdziwej" produkcji zastanawiałbym się np nad np mailgunem lub innym dedykowanym rozwiązaniem, same wiadomości wysylałbym również z pomocą kolejki, message brokera np RabbitMq.
 
Przy okazji logowania z powodzeniem lub bez powodzenia, zapisywane są logi w bazie danych. Są one zatrzymywane do późniejszej ewentualnej analizy.

Sam docelowy serwer ma zabezpieczenia przed DDOS. Na produkcji zastanawiałbym się też np nad cloudflare.

Nie starczyło mi niestety czasu żeby zaimplementować login throttling, nie zdążyłem się wcześniej zapoznać z tą funkcjonalnośćią, ponieważ została wprowadzona dopiero w symfony 5.2

