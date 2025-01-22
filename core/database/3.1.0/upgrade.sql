-- 
-- `ResellersCenter_Invoices`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_Invoices` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `reseller_id`   int(10) unsigned NOT NULL,
    `relinvoice_id` int(10) unsigned NOT NULL,
    `userid`        int(10) unsigned NOT NULL,
    `invoicenum`    TEXT NOT NULL,
    `date`          DATE DEFAULT '0000-00-00 00:00:00',
    `duedate`       DATE DEFAULT '0000-00-00 00:00:00',
    `datepaid`      DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `last_capture_attempt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `subtotal`      DECIMAL(16,2) NOT NULL,
    `credit`        DECIMAL(16,2) NOT NULL,
    `tax`           DECIMAL(16,2) NOT NULL,
    `tax2`          DECIMAL(16,2) NOT NULL,
    `total`         DECIMAL(16,2) NOT NULL,
    `taxrate`       DECIMAL(16,2) NOT NULL,
    `taxrate2`      DECIMAL(16,2) NOT NULL,
    `status`        TEXT NOT NULL,
    `paymentmethod` TEXT NOT NULL,
    `notes`         TEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `reseller_id` (`reseller_id`),
    KEY `userid`      (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_InvoiceItems`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_InvoiceItems` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `reseller_id`   int(10) unsigned NOT NULL,
    `invoice_id`    int(10) unsigned NOT NULL,
    `userid`        int(10) unsigned NOT NULL,
    `type`          varchar(30) NOT NULL,
    `relid`         int(10) unsigned NOT NULL,
    `description`   TEXT NOT NULL,
    `amount`        DECIMAL(16,2) NOT NULL,
    `taxed`         int(1) NOT NULL,
    `duedate`       timestamp DEFAULT '0000-00-00 00:00:00',
    `paymentmethod` TEXT NOT NULL,
    `notes`         TEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `invoice_id`  (`invoice_id`),
    KEY `reseller_id` (`reseller_id`),
    KEY `userid`      (`userid`),
    KEY `relid`       (`relid`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_Transctions`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_Transactions` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `userid`        int(10) unsigned NOT NULL,
    `currency`      int(10) unsigned NOT NULL,
    `gateway`       TEXT NOT NULL,
    `date`          DATETIME,
    `description`   TEXT NOT NULL,
    `amountin`      DECIMAL(16,2) NOT NULL,
    `fees`          DECIMAL(16,2) NOT NULL,
    `amountout`     DECIMAL(16,2) NOT NULL,
    `rate`          DECIMAL(16,2) NOT NULL,
    `transid`       TEXT NOT NULL,
    `invoice_id`    int(10) unsigned NOT NULL,
    `refundid`      int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `invoice_id`    (`invoice_id`),
    KEY `refundid`      (`refundid`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;

--
-- `ResellersCenter_PaymentGatewaysLogs`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_PaymentGatewaysLogs` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `reseller_id`   int(10) unsigned NOT NULL,
    `date`          DATETIME,
    `gateway`       TEXT NOT NULL,
    `data`          TEXT NOT NULL,
    `result`        TEXT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `reseller_id`    (`reseller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;