-- Primary key for JANUS entities (ported from manage/patch-002.sql)
ALTER TABLE `janus__entity` ADD PRIMARY KEY (`eid`, `revisionid`);