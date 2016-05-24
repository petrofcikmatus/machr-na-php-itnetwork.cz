# Machr na PHP - Registrácia a prihlasovanie

**Link:** http://www.itnetwork.cz/php/diskuzni-forum-php-webtvorba/machr-na-php-registrace-prihlasovani-573df6ca3c869

### Štruktúra databázy

Používam MySQL databázu.

```sql
/* Tabuľka užívateľov. */
CREATE TABLE users (
  user_id                   SERIAL        PRIMARY KEY,
  user_email                VARCHAR(128)  NOT NULL UNIQUE,
  user_password_hash        VARCHAR(128)  NOT NULL,
  user_password_salt        VARCHAR(128)  NOT NULL,
  user_created_at           TIMESTAMP     NOT NULL DEFAULT now(),
  user_is_actived           BOOLEAN       NOT NULL DEFAULT FALSE,
  user_activation_hash      VARCHAR(128)  NOT NULL
  -- todo: pridať napríklad údaje ako meno užívateľa a podobne
);

/* Tabuľka aktívnych prihlásení. */
CREATE TABLE active_logins (
  active_login_id         SERIAL           PRIMARY KEY,
  active_login_user_id    BIGINT UNSIGNED  NOT NULL REFERENCES users(user_id),
  active_login_token_hash VARCHAR(128)     NOT NULL UNIQUE,
  active_login_created_at TIMESTAMP        NOT NULL DEFAULT now()
  -- todo: pridať napríklad údaje o prehliadači, posledná IP a podobne
);

/* Tabuľka chybných prihlásení. Cieľom je hlavne zabrániť silovému útoku na hádanie hesiel. */
CREATE TABLE failed_logins (
  failed_login_id         SERIAL           PRIMARY KEY,
  failed_login_user_id    BIGINT UNSIGNED  NOT NULL REFERENCES users(user_id),
  failed_login_created_at TIMESTAMP        NOT NULL DEFAULT now()
  -- todo: tabuľka sa pri úspešnom prihlásení vymaže, ale do budúcna sa môže použiť aj inak
);
```