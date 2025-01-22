--
-- Insert default documentation to database only if table is empty
--
INSERT INTO `ResellersCenter_Documentations` (`name`, `content`, `pdfpath`, `created_at`) VALUES ('#name#', '#content#', '#pdfpath#', NOW());
