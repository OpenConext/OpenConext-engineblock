-- Add deleted column for deleting ARP rules in JANUS 1.10.0
ALTER TABLE `janus__arp` ADD `deleted` char(25) NOT NULL AFTER `updated`;