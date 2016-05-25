# Machr na PHP - Registrácia a prihlasovanie

**Link:** http://www.itnetwork.cz/php/diskuzni-forum-php-webtvorba/machr-na-php-registrace-prihlasovani-573df6ca3c869

## Štruktúra databázy

Používam MySQL databázu.

```sql
/* Tabuľka užívateľov. Do tejto tabuľky by som nedával veci ako meno, priezvisko, ... na to by slúžila tabuľka users_meta */
CREATE TABLE users (
  id                  SERIAL       PRIMARY KEY,
  email               VARCHAR(128) NOT NULL UNIQUE,
  password_hash       VARCHAR(128) NOT NULL,
  password_salt       VARCHAR(128) NOT NULL,
  is_actived          BOOLEAN      NOT NULL DEFAULT FALSE,
  activation_key_hash VARCHAR(128) NOT NULL DEFAULT '',
  created_at          TIMESTAMP    NOT NULL DEFAULT now()
);

/* Tabuľka obnovovacích kľúčov. */
CREATE TABLE recovery_keys (
  id         SERIAL PRIMARY KEY,
  uid        BIGINT UNSIGNED NOT NULL REFERENCES users(id),
  key_hash   VARCHAR(128)    NOT NULL,
  created_at TIMESTAMP       NOT NULL DEFAULT now()
);

/* Tabuľka aktívnych prihlásení. */
CREATE TABLE active_logins (
  id         SERIAL          PRIMARY KEY,
  uid        BIGINT UNSIGNED NOT NULL REFERENCES users(id),
  token_hash VARCHAR(128)    NOT NULL UNIQUE,
  created_at TIMESTAMP       NOT NULL DEFAULT now()
  -- todo: pridať napríklad údaje o prehliadači, posledná IP a podobne
);

/* Tabuľka chybných prihlásení. Cieľom je hlavne zabrániť silovému útoku na hádanie hesiel.
 * Tabuľka sa pri úspešnom prihlásení pre daného užívateľa vymaže, ale do budúcna sa môže použiť aj inak.
 */
CREATE TABLE failed_logins (
  id         SERIAL          PRIMARY KEY,
  uid        BIGINT UNSIGNED NOT NULL REFERENCES users(id),
  created_at TIMESTAMP       NOT NULL DEFAULT now()
  -- todo: pridať napríklad údaje o prehliadači, posledná IP a podobne
);
```