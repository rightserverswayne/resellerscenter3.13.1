--
-- `ResellersCenter_Resellers`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_Resellers` (
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `client_id`  int(10) unsigned NOT NULL,
    `group_id`   int(10) unsigned NOT NULL,
    `lastlogin`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    KEY `client_id` (`client_id`),
    KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_ResellersSettings`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_ResellersSettings` (
    `reseller_id` int(10) unsigned NOT NULL DEFAULT '0',
    `private`     int(1) unsigned NOT NULL DEFAULT '0',
    `setting`     VARCHAR(255) COLLATE #collation# NOT NULL,
    `value`       TEXT COLLATE #collation#,
    PRIMARY KEY (`reseller_id`, `setting`, `private`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_ResellersClients`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_ResellersClients` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `client_id`     int(10) unsigned NOT NULL,
    `reseller_id`   int(10) unsigned NOT NULL,
    `created_at`    timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at`    timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    KEY `client_id` (`client_id`),
    KEY `reseller_id` (`reseller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_ResellersServices`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_ResellersServices` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `reseller_id`   int(10) unsigned NOT NULL,
    `type`          enum('addon','hosting','domain') NOT NULL,
    `relid`         int(10) unsigned NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    KEY `reseller_id` (`reseller_id`),
    KEY `relid`  (`relid`),
    KEY `type`  (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_ResellersTickets`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_ResellersTickets` (
    `ticket_id`   INT(10) NOT NULL,
    `reseller_id` INT(10) NOT NULL,
    PRIMARY KEY (`ticket_id`),
    KEY `reseller_id` (`reseller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_ResellersProfits`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_ResellersProfits` (
    `id`                int(10) unsigned NOT NULL AUTO_INCREMENT,
    `reseller_id`       int(10) unsigned NOT NULL,
    `invoiceitem_id`    int(10) unsigned NOT NULL,
    `service_id`        int(10) unsigned NOT NULL,
    `amount`            DECIMAL(16,2),
    `collected`         int(1) unsigned NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    KEY `reseller_id` (`reseller_id`),
    KEY `invoiceitem_id`  (`invoiceitem_id`),
    KEY `service_id`  (`service_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_ResellersPricing`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_ResellersPricing` (
    `reseller_id`   int(10) unsigned NOT NULL,
    `relid`         int(10) unsigned NOT NULL,
    `type`          enum('product','addon','domainregister','domaintransfer','domainrenew') NOT NULL,
    `currency`      int(10) unsigned NOT NULL,
    `billingcycle`  VARCHAR(40) NOT NULL, 
    `value`         DECIMAL(16,2),
    KEY `reseller_id` (`reseller_id`),
    KEY `relid`  (`relid`),
    KEY `type`  (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_Groups`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_Groups` (
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`      VARCHAR(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;


--
-- `ResellersCenter_GroupsContents`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_GroupsContents` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `group_id`      int(10) unsigned NOT NULL,
    `relid`         int(10) unsigned NOT NULL,
    `type`          enum('product','addon','domainregister','domaintransfer','domainrenew') NOT NULL,
    PRIMARY KEY (`id`),
    KEY `group_id` (`group_id`),
    KEY `relid` (`relid`),
    KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;



--
-- `ResellersCenter_GroupsContentsSettings`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_GroupsContentsSettings` (
    `relid`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `group_id`      int(10) unsigned NOT NULL,
    `setting`       VARCHAR(255) NOT NULL,
    `value`         TEXT COLLATE #collation#,
    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`relid`, `group_id`, `setting`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;



--
-- `ResellersCenter_GroupsContentsPricing`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_GroupsContentsPricing` (
    `relid`         int(10) unsigned NOT NULL,
    `type`          VARCHAR(255) NOT NULL,
    `currency`      int(10) unsigned NOT NULL,
    `billingcycle`  VARCHAR(40) NOT NULL,
    `value`         DECIMAL(16,2),
    PRIMARY KEY (`relid`, `type`, `currency`, `billingcycle`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

-- 
-- `ResellersCenter_BrandedInvoices`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_BrandedInvoices` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `reseller_id`   int(10) unsigned NOT NULL,
    `invoice_id`    int(10) NOT NULL,
    `client_id`     int(10) unsigned NOT NULL,
    `invoicenum`    VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `reseller_id` (`reseller_id`),
    KEY `invoice_id` (`invoice_id`),
    KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE #collation#;

-- 
-- `ResellersCenter_Invoices`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_Invoices` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `reseller_id`   int(10) unsigned NOT NULL,
    `relid`         int(10) unsigned NOT NULL,
    `client_id`     int(10) unsigned NOT NULL,
    `invoicenum`    VARCHAR(255) NOT NULL,
    `date`          timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `duedate`       timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `datepaid`      timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `total`         int(10) unsigned NOT NULL,
    `status`        enum('paid','unpaid','cancelled','refunded') NOT NULL,
    `paymentmethod`       VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `reseller_id` (`reseller_id`),
    KEY `client_id` (`client_id`),
    KEY `relid` (`relid`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_InvoiceItems`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_InvoiceItems` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `invoice_id`    int(10) unsigned NOT NULL,
    `type`          enum('addon','hosting','domain') NOT NULL,
    `relid`         int(10) unsigned NOT NULL,
    `description`   TEXT,
    `amount`        DECIMAL(16,2),
    `taxed`         int(1),
    PRIMARY KEY (`id`),
    KEY `invoice_id` (`invoice_id`),
    KEY `relid`  (`relid`),
    KEY `type`  (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_PaymentGateways`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_PaymentGateways` (
    `reseller_id`   INT(11),
    `gateway`       VARCHAR(255),
    `setting`       VARCHAR(255),
    `value`         TEXT,
    KEY `reseller_id`  (reseller_id),
    KEY `gateway`  (`gateway`),
    KEY `setting`  (`setting`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_EmailTemplates`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_EmailTemplates` (
    `id`            INT(10) unsigned NOT NULL AUTO_INCREMENT,
    `reseller_id`   INT(10),
    `name`          TEXT,
    `subject`       TEXT,
    `message`       TEXT,
    `language`      TEXT,
    `created_at`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    KEY `reseller_id`  (`reseller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_Session`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_SessionStorage` (
    `key`         VARCHAR(255) NOT NULL,
    `time`        INT(10) NOT NULL,
    `value`       BLOB NOT NULL,
    `created_at`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_Logs`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_Logs` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `admin_id`      INT(10),
    `reseller_id`   INT(10),
    `client_id`     INT(10),
    `description`   TEXT,
    `type`          enum('info','warning','error') NOT NULL,
    `created_at`  timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`),
    KEY `admin_id`  (`admin_id`),
    KEY `reseller_id`  (`reseller_id`),
    KEY `client_id`  (`client_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_Documentations`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_Documentations` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`          TEXT,
    `content`       TEXT,
    `pdfpath`       TEXT,
    `created_at`    timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at`    timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 DEFAULT COLLATE #collation#;

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