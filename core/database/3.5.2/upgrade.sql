ALTER TABLE `ResellersCenter_ResellersServices` ADD KEY `type` (`type`);
ALTER TABLE `ResellersCenter_ResellersProfits` ADD KEY `service_id` (`service_id`);
ALTER TABLE `ResellersCenter_GroupsContents` ADD KEY `type` (`type`);
ALTER TABLE `ResellersCenter_BrandedInvoices` ADD KEY `client_id` (`client_id`);
ALTER TABLE `ResellersCenter_InvoiceItems` ADD KEY `type` (`type`);
ALTER TABLE `ResellersCenter_PaymentGateways` ADD KEY `reseller_id` (`reseller_id`);