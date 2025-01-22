--
-- `ResellersCenter_CreditLine`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_CreditLine` (
    `id`            int(10) NOT NULL AUTO_INCREMENT,
    `client_id`     int(10) NOT NULL,
    `reseller_id`   int(10) unsigned NULL,
    `limit`         DECIMAL(16,2) NOT NULL,
    `usage`        DECIMAL(16,2) DEFAULT 0,
    PRIMARY KEY (`id`),
    FOREIGN Key (`client_id`) REFERENCES tblclients(id) ON DELETE CASCADE,
    FOREIGN Key (`reseller_id`) REFERENCES ResellersCenter_Resellers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_CreditLineHistory`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_CreditLineHistory` (
    `id`                int(10) NOT NULL AUTO_INCREMENT,
    `credit_line_id`    int(10) NOT NULL,
    `balance`           DECIMAL(16,2) NOT NULL,
    `amount`            DECIMAL(16,2) NOT NULL,
    `invoice_item_id`   int(10) NOT NULL,
    `invoice_type`      ENUM('reseller', 'whmcs') NOT NULL,
    `date`              timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN Key (`credit_line_id`) REFERENCES ResellersCenter_CreditLine(id) ON DELETE CASCADE
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_ResellersClientsSettings`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_ResellersClientsSettings` (
    `id`                    int(10) NOT NULL AUTO_INCREMENT,
    `reseller_client_id`    int(10) unsigned NOT NULL,
    `setting`               VARCHAR(250) NOT NULL,
    `value`                 VARCHAR(250) NULL,
    PRIMARY KEY (`id`),
    FOREIGN Key (`reseller_client_id`) REFERENCES ResellersCenter_ResellersClients(id) ON DELETE CASCADE
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_GroupsSettings`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_GroupsSettings` (
    `id`                    int(10) NOT NULL AUTO_INCREMENT,
    `group_id`              int(10) unsigned NOT NULL,
    `setting`               VARCHAR(250) NOT NULL,
    `value`                 VARCHAR(250) NULL,
    PRIMARY KEY (`id`),
    FOREIGN Key (`group_id`) REFERENCES ResellersCenter_Groups(id) ON DELETE CASCADE
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 DEFAULT COLLATE #collation#;

--
-- `FIX issue 1671`
--
UPDATE ResellersCenter_PaymentGateways SET setting='secretApiKey' WHERE setting = 'secredApiKey' AND gateway = 'Stripe';