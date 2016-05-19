# Machr na PHP - Registrace, přihlašování

**Link:** http://www.itnetwork.cz/php/diskuzni-forum-php-webtvorba/machr-na-php-registrace-prihlasovani-573df6ca3c869

### Zadanie

Vaším úkolem v této soutěži bude vytvořit jednoduchou stránku a přidat na ní registrační formulář. Po registraci bude třeba účet aktivovat pomocí odkazu zaslaného na email. K tomu přidáte možnost přihlášení pro zaregistrované (a aktivované) účty + možnost obnovení hesla pomocí emailu.
Během vyplňování registračního formuláře budete pomocí JavaScriptu kontrolovat platnost vyplňovaných údajů (jako např. jak je dlouhé heslo, zda se shoduje s potvrzením hesla, jestli uživatel nezadal místo emailu nějaký nesmysl...)
Hodnotit budu kód (JavaScript i PHP) a funkčnost (zda vše funguje jak má, jestli stránka nevyhodí nějakou chybu) - a to půl na půl.
Maximum je sto bodů.

K JavaScriptu můžete použít čistou jQuery.

### Štruktúra databázy

Používam MySQL databázu.

```sql
CREATE TABLE users (
  user_id                   SERIAL        PRIMARY KEY,
  user_email                VARCHAR(128)  NOT NULL UNIQUE,
  user_password_hash        VARCHAR(128)  NOT NULL,
  user_password_salt        VARCHAR(128)  NOT NULL,
  user_is_actived           BOOLEAN       NOT NULL DEFAULT FALSE,
  user_activation_hash      VARCHAR(128)  NOT NULL,
  user_failed_login_counter INTEGER       NOT NULL DEFAULT 0,
  user_last_failed_login    TIMESTAMP              DEFAULT NULL,
  user_name                 VARCHAR(64)   NOT NULL
);

-- todo: add timestamp for registration and last login, maybe active logins table?
```
