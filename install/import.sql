ALTER TABLE civicrm_contribution_page
ADD COLUMN is_direct_debit INT (10) unsigned DEFAULT NULL COMMENT 'Does this contribution page accept direct debit payments?';