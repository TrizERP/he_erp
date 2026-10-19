-- Direct SQL (no seeder) for sub_institute_id 133:
--   Placement = level-2 menu under "Institute", its pages = level-3 (dropdown); the master pages are listed in the
--   right "Show Sidebar" panel (they open when Placement is clicked). Also Admission workflow + Fees Waiver menus.
-- Re-runnable. If an earlier version of this script was run (Placement as level-1 with the two group menus) it is
-- converted in place first, keeping the menu ids and rights.
-- Needs the level-1 menu "Institute" with the level-2 groups "Admission" and "Fees" under it (first SELECT must show
-- institute_id and must_be_2 = 2). Admission pages and Fees Waiver go in as level-3 inside those groups.
-- Back up tblmenumaster / tblgroupwise_rights / rightside_menumaster first, then run on the right database.

SET @sid = 133;
SET @cid = (SELECT client_id FROM school_setup WHERE Id = @sid);
-- @inst = the level-1 menu that holds the "Admission" and "Fees" dropdowns (shown as "Institute" in the main bar).
-- On this database that is id 1 (Institute), with Fees = 6 and Admission = 173 under it.
SET @inst = (SELECT parent_menu_id FROM tblmenumaster WHERE name = 'Admission' AND level = 2 LIMIT 1);
SET @inst = COALESCE(@inst, 1);

SELECT @inst AS institute_id, (SELECT name FROM tblmenumaster WHERE id = @inst) AS institute_name, @cid AS client_id,
       (SELECT COUNT(DISTINCT name) FROM tblmenumaster WHERE level = 2 AND parent_menu_id = @inst AND name IN ('Admission','Fees')) AS must_be_2;

START TRANSACTION;

-- 0. convert the earlier layout (level-1 Placement + group menus) in place
SET @old = (SELECT id FROM tblmenumaster WHERE name = 'Placement' AND level = 1 AND parent_menu_id = 0
            AND icon = 'mdi mdi-briefcase-check' ORDER BY id LIMIT 1);
UPDATE tblmenumaster c JOIN tblmenumaster g ON g.id = c.parent_menu_id AND g.level = 2 AND g.parent_menu_id = @old
SET c.parent_menu_id = @old
WHERE g.name IN ('Masters & Setup', 'Policy & Eligibility');
DELETE r FROM tblgroupwise_rights r JOIN tblmenumaster g ON g.id = r.menu_id
WHERE g.parent_menu_id = @old AND g.level = 2 AND g.name IN ('Masters & Setup', 'Policy & Eligibility');
DELETE FROM tblmenumaster WHERE parent_menu_id = @old AND level = 2 AND name IN ('Masters & Setup', 'Policy & Eligibility');
DELETE FROM rightside_menumaster WHERE sub_institute_id = CAST(@sid AS CHAR)
  AND main_menu_id IN (SELECT id FROM tblmenumaster WHERE id = @old OR parent_menu_id = @old);
UPDATE tblmenumaster SET level = 3 WHERE parent_menu_id = @old AND level = 2;
UPDATE tblmenumaster SET level = 2, parent_menu_id = @inst WHERE id = @old AND @inst IS NOT NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_menu;
CREATE TEMPORARY TABLE tmp_menu (
  lvl INT, name VARCHAR(150), parent_name VARCHAR(150), link VARCHAR(200), icon VARCHAR(100),
  sort_order INT, menu_type VARCHAR(20), parent_id INT NULL, menu_id INT NULL
);
INSERT INTO tmp_menu (lvl, name, parent_name, link, icon, sort_order, menu_type) VALUES
(2,'Placement','Institute','javascript:void(0);','mdi mdi-briefcase-check',NULL,'ENTRY'),
(3,'Dashboard','Placement','placement.dashboard','mdi mdi-view-dashboard',1,'ENTRY'),
(3,'Companies','Placement','placement.companies.index','mdi mdi-domain',2,'ENTRY'),
(3,'Reports','Placement','placement.reports.index','mdi mdi-chart-bar',3,'ENTRY'),
(3,'Job Drives','Placement','placement.drives.index','mdi mdi-briefcase-search',4,'ENTRY'),
(3,'Profile Verification','Placement','placement.verification.index','mdi mdi-account-check',5,'ENTRY'),
(3,'Offers','Placement','placement.offers.index','mdi mdi-handshake',6,'ENTRY'),
(3,'Training & Mock Tests','Placement','placement.training.index','mdi mdi-school',7,'ENTRY'),
(3,'Resume Reviews','Placement','placement.resume_reviews.index','mdi mdi-file-document-box',8,'ENTRY'),
(3,'Recruiter Requirements','Placement','placement.requirements.index','mdi mdi-account-search',9,'ENTRY'),
(3,'Other Outcomes','Placement','placement.outcomes.index','mdi mdi-flag-checkered',10,'ENTRY'),
(3,'Announcements','Placement','placement.announcements.index','mdi mdi-bullhorn',11,'ENTRY'),
(3,'Communication','Placement','placement.notifications.index','mdi mdi-email-outline',12,'ENTRY'),
(3,'Settings & Audit','Placement','placement.settings.index','mdi mdi-tune',13,'ENTRY'),
(3,'My Placement Profile','Placement','placement.student.profile','mdi mdi-account',14,'ENTRY'),
(3,'Drives For Me','Placement','placement.student.drives','mdi mdi-briefcase-search',15,'ENTRY'),
(3,'My Applications','Placement','placement.student.applications','mdi mdi-clipboard-text-outline',16,'ENTRY'),
(3,'My Rounds','Placement','placement.student.rounds','mdi mdi-flag-checkered',17,'ENTRY'),
(3,'My Offers','Placement','placement.student.offers','mdi mdi-handshake',18,'ENTRY'),
(3,'Notices','Placement','placement.student.notices','mdi mdi-bullhorn',19,'ENTRY'),
(3,'My Training','Placement','placement.student.training','mdi mdi-school',20,'ENTRY'),
(3,'Masters','Placement','placement.masters.index','mdi mdi-chevron-right',21,'MASTER'),
(3,'Placement Seasons','Placement','placement.seasons.index','mdi mdi-chevron-right',22,'MASTER'),
(3,'Templates','Placement','placement.templates.index','mdi mdi-chevron-right',23,'MASTER'),
(3,'Coordinators','Placement','placement.coordinators.index','mdi mdi-chevron-right',24,'MASTER'),
(3,'Placement Policy','Placement','placement.policies.index','mdi mdi-chevron-right',25,'MASTER'),
(3,'Eligibility Templates','Placement','placement.eligibility.index','mdi mdi-chevron-right',26,'MASTER'),
(3,'Admission Dashboard','Admission','admission.dashboard.index','mdi mdi-view-dashboard',50,'ENTRY'),
(3,'Application Review','Admission','admission.review.index','mdi mdi-clipboard-text-outline',51,'ENTRY'),
(3,'Document Verification','Admission','admission.documents.index','mdi mdi-file-document-box',52,'ENTRY'),
(3,'Merit / Selection','Admission','admission.merit.index','mdi mdi-trophy-outline',53,'ENTRY'),
(3,'Offer / Seat Allocation','Admission','admission.offers.index','mdi mdi-handshake',54,'ENTRY'),
(3,'Admission Fee','Admission','admission.fees.index','mdi mdi-cash',55,'ENTRY'),
(3,'Confirmation Checklist','Admission','admission.confirm.index','mdi mdi-clipboard-check',56,'ENTRY'),
(3,'Admitted Students','Admission','admission.admitted.index','mdi mdi-school',57,'ENTRY'),
(3,'Admission Workflow Reports','Admission','admission.reports.index','mdi mdi-chart-bar',58,'ENTRY'),
(3,'Admission Settings','Admission','admission.settings.index','mdi mdi-tune',59,'ENTRY'),
(3,'Fees Waiver','Fees','fees_waiver.index','mdi mdi-cash',NULL,'ENTRY');

-- level 2 (Placement under Institute)
UPDATE tmp_menu t SET t.parent_id = CASE WHEN t.parent_name = 'Institute' THEN @inst
  ELSE (SELECT p.id FROM tblmenumaster p WHERE p.name = t.parent_name AND p.level = 1 AND p.parent_menu_id = 0 LIMIT 1) END
WHERE t.lvl = 2;
UPDATE tmp_menu t SET t.sort_order = (SELECT COALESCE(MAX(m.sort_order), 0) + 1 FROM tblmenumaster m WHERE m.parent_menu_id = t.parent_id)
WHERE t.lvl = 2 AND t.sort_order IS NULL;
INSERT INTO tblmenumaster (name, parent_menu_id, level, description, status, sort_order, link, icon, sub_institute_id, client_id, menu_type, site_map_name, menu_path, created_at, updated_at)
SELECT t.name, t.parent_id, t.lvl, t.name, 1, t.sort_order, t.link, t.icon, CAST(@sid AS CHAR), @cid, t.menu_type, t.name, t.name, NOW(), NOW()
FROM tmp_menu t
WHERE t.lvl = 2 AND t.parent_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM tblmenumaster m WHERE m.name = t.name AND m.level = t.lvl AND m.parent_menu_id = t.parent_id);
UPDATE tmp_menu t JOIN tblmenumaster m ON m.name = t.name AND m.level = t.lvl AND m.parent_menu_id = t.parent_id
SET t.menu_id = m.id WHERE t.lvl = 2;

-- level 3 (Placement pages, Admission pages, Fees Waiver)
UPDATE tmp_menu t SET t.parent_id = (SELECT p.id FROM tblmenumaster p WHERE p.name = t.parent_name AND p.level = 2 AND p.parent_menu_id = @inst LIMIT 1)
WHERE t.lvl = 3;
UPDATE tmp_menu t SET t.sort_order = (SELECT COALESCE(MAX(m.sort_order), 0) + 1 FROM tblmenumaster m WHERE m.parent_menu_id = t.parent_id)
WHERE t.lvl = 3 AND t.sort_order IS NULL AND t.parent_id IS NOT NULL;
INSERT INTO tblmenumaster (name, parent_menu_id, level, description, status, sort_order, link, icon, sub_institute_id, client_id, menu_type, site_map_name, menu_path, created_at, updated_at)
SELECT t.name, t.parent_id, t.lvl, t.name, 1, t.sort_order, t.link, t.icon, CAST(@sid AS CHAR), @cid, t.menu_type, t.name, t.name, NOW(), NOW()
FROM tmp_menu t
WHERE t.lvl = 3 AND t.parent_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM tblmenumaster m WHERE m.name = t.name AND m.level = t.lvl AND m.parent_menu_id = t.parent_id);
UPDATE tmp_menu t JOIN tblmenumaster m ON m.name = t.name AND m.level = t.lvl AND m.parent_menu_id = t.parent_id
SET t.menu_id = m.id WHERE t.lvl = 3;

-- menus that already existed: make sure 133 and its client are in the lists, the menu is active and master pages are MASTER
UPDATE tblmenumaster m JOIN tmp_menu t ON t.menu_id = m.id
SET m.status = 1,
    m.menu_type = t.menu_type,
    m.icon = t.icon,
    m.sub_institute_id = IF(FIND_IN_SET(@sid, m.sub_institute_id), m.sub_institute_id, CONCAT_WS(',', NULLIF(m.sub_institute_id,''), @sid)),
    m.client_id = IF(FIND_IN_SET(@cid, m.client_id), m.client_id, CONCAT_WS(',', NULLIF(m.client_id,''), @cid)),
    m.updated_at = NOW();

-- profiles missing in 133 (Admin and Student are never created)
INSERT INTO tbluserprofilemaster (parent_id, name, description, sort_order, status, sub_institute_id)
SELECT 0, n.name, n.name, (SELECT COALESCE(MAX(x.sort_order),0)+1 FROM (SELECT sort_order FROM tbluserprofilemaster WHERE sub_institute_id=@sid) x), 1, @sid
FROM (SELECT 'TPO' AS name
  UNION ALL SELECT 'Principal' AS name
  UNION ALL SELECT 'Placement Coordinator' AS name
  UNION ALL SELECT 'Admission Officer' AS name
  UNION ALL SELECT 'Admission Approver' AS name
  UNION ALL SELECT 'Accounts Operator' AS name) n
WHERE NOT EXISTS (SELECT 1 FROM tbluserprofilemaster p WHERE p.sub_institute_id=@sid AND LOWER(p.name)=LOWER(n.name));

DROP TEMPORARY TABLE IF EXISTS tmp_acl;
CREATE TEMPORARY TABLE tmp_acl (menu_name VARCHAR(150), profile_name VARCHAR(100), v TINYINT, a TINYINT, e TINYINT, d TINYINT);
INSERT INTO tmp_acl VALUES
('Placement','Admin',1,1,1,1),
('Dashboard','Admin',1,1,1,1),
('Companies','Admin',1,1,1,1),
('Reports','Admin',1,1,1,1),
('Job Drives','Admin',1,1,1,1),
('Profile Verification','Admin',1,1,1,1),
('Offers','Admin',1,1,1,1),
('Training & Mock Tests','Admin',1,1,1,1),
('Resume Reviews','Admin',1,1,1,1),
('Recruiter Requirements','Admin',1,1,1,1),
('Other Outcomes','Admin',1,1,1,1),
('Announcements','Admin',1,1,1,1),
('Communication','Admin',1,1,1,1),
('Settings & Audit','Admin',1,1,1,1),
('Masters','Admin',1,1,1,1),
('Placement Seasons','Admin',1,1,1,1),
('Templates','Admin',1,1,1,1),
('Coordinators','Admin',1,1,1,1),
('Placement Policy','Admin',1,1,1,1),
('Eligibility Templates','Admin',1,1,1,1),
('Placement','TPO',1,1,1,1),
('Dashboard','TPO',1,1,1,1),
('Companies','TPO',1,1,1,1),
('Reports','TPO',1,1,1,1),
('Job Drives','TPO',1,1,1,1),
('Profile Verification','TPO',1,1,1,1),
('Offers','TPO',1,1,1,1),
('Training & Mock Tests','TPO',1,1,1,1),
('Resume Reviews','TPO',1,1,1,1),
('Recruiter Requirements','TPO',1,1,1,1),
('Other Outcomes','TPO',1,1,1,1),
('Announcements','TPO',1,1,1,1),
('Communication','TPO',1,1,1,1),
('Settings & Audit','TPO',1,1,1,1),
('Masters','TPO',1,1,1,1),
('Placement Seasons','TPO',1,1,1,1),
('Templates','TPO',1,1,1,1),
('Coordinators','TPO',1,1,1,1),
('Placement Policy','TPO',1,1,1,1),
('Eligibility Templates','TPO',1,1,1,1),
('Placement','Principal',1,0,0,0),
('Dashboard','Principal',1,0,0,0),
('Companies','Principal',1,0,0,0),
('Reports','Principal',1,0,0,0),
('Job Drives','Principal',1,0,0,0),
('Profile Verification','Principal',1,0,0,0),
('Offers','Principal',1,0,0,0),
('Training & Mock Tests','Principal',1,0,0,0),
('Resume Reviews','Principal',1,0,0,0),
('Recruiter Requirements','Principal',1,0,0,0),
('Other Outcomes','Principal',1,0,0,0),
('Announcements','Principal',1,0,0,0),
('Communication','Principal',1,0,0,0),
('Settings & Audit','Principal',1,0,0,0),
('Masters','Principal',1,0,0,0),
('Placement Seasons','Principal',1,0,0,0),
('Templates','Principal',1,0,0,0),
('Coordinators','Principal',1,0,0,0),
('Placement Policy','Principal',1,0,0,0),
('Eligibility Templates','Principal',1,0,0,0),
('Placement','Placement Coordinator',1,0,0,0),
('Dashboard','Placement Coordinator',1,0,0,0),
('Companies','Placement Coordinator',1,0,0,0),
('Reports','Placement Coordinator',1,0,0,0),
('Job Drives','Placement Coordinator',1,0,0,0),
('Profile Verification','Placement Coordinator',1,0,0,0),
('Offers','Placement Coordinator',1,0,0,0),
('Training & Mock Tests','Placement Coordinator',1,0,0,0),
('Resume Reviews','Placement Coordinator',1,0,0,0),
('Recruiter Requirements','Placement Coordinator',1,0,0,0),
('Other Outcomes','Placement Coordinator',1,0,0,0),
('Announcements','Placement Coordinator',1,0,0,0),
('Communication','Placement Coordinator',1,0,0,0),
('Settings & Audit','Placement Coordinator',1,0,0,0),
('Masters','Placement Coordinator',1,0,0,0),
('Placement Seasons','Placement Coordinator',1,0,0,0),
('Templates','Placement Coordinator',1,0,0,0),
('Coordinators','Placement Coordinator',1,0,0,0),
('Placement Policy','Placement Coordinator',1,0,0,0),
('Eligibility Templates','Placement Coordinator',1,0,0,0),
('Placement','Student',1,0,0,0),
('My Placement Profile','Student',1,0,0,0),
('Drives For Me','Student',1,0,0,0),
('My Applications','Student',1,0,0,0),
('My Rounds','Student',1,0,0,0),
('My Offers','Student',1,0,0,0),
('Notices','Student',1,0,0,0),
('My Training','Student',1,0,0,0),
('Admission Dashboard','Admin',1,1,1,1),
('Admission Dashboard','Admission Officer',1,1,1,0),
('Admission Dashboard','Admission Approver',1,0,0,0),
('Admission Dashboard','Accounts Operator',1,0,0,0),
('Application Review','Admin',1,1,1,1),
('Application Review','Admission Officer',1,1,1,0),
('Application Review','Admission Approver',1,0,0,0),
('Document Verification','Admin',1,1,1,1),
('Document Verification','Admission Officer',1,1,1,0),
('Document Verification','Admission Approver',1,0,0,0),
('Merit / Selection','Admin',1,1,1,1),
('Merit / Selection','Admission Officer',1,1,1,0),
('Merit / Selection','Admission Approver',1,0,0,0),
('Offer / Seat Allocation','Admin',1,1,1,1),
('Offer / Seat Allocation','Admission Officer',1,1,1,0),
('Offer / Seat Allocation','Admission Approver',1,0,0,0),
('Admission Fee','Admin',1,1,1,1),
('Admission Fee','Admission Officer',1,1,1,0),
('Admission Fee','Accounts Operator',1,0,0,0),
('Confirmation Checklist','Admin',1,1,1,1),
('Confirmation Checklist','Admission Officer',1,1,1,0),
('Admitted Students','Admin',1,1,1,1),
('Admitted Students','Admission Officer',1,1,1,0),
('Admitted Students','Admission Approver',1,0,0,0),
('Admitted Students','Accounts Operator',1,0,0,0),
('Admission Workflow Reports','Admin',1,1,1,1),
('Admission Workflow Reports','Admission Officer',1,1,1,0),
('Admission Workflow Reports','Admission Approver',1,0,0,0),
('Admission Workflow Reports','Accounts Operator',1,0,0,0),
('Admission Settings','Admin',1,1,1,1),
('Fees Waiver','Admin',1,1,1,0);

-- replace rights of these profiles on these menus for 133
DELETE g FROM tblgroupwise_rights g
JOIN tbluserprofilemaster p ON p.id = g.profile_id AND p.sub_institute_id = @sid
JOIN tmp_menu t ON t.menu_id = g.menu_id
WHERE g.sub_institute_id = @sid
  AND LOWER(p.name) IN ('admin','tpo','principal','placement coordinator','student','admission officer','admission approver','accounts operator');

INSERT INTO tblgroupwise_rights (menu_id, profile_id, sub_institute_id, can_view, can_add, can_edit, can_delete)
SELECT t.menu_id, p.id, @sid, a.v, a.a, a.e, a.d
FROM tmp_acl a
JOIN tmp_menu t ON t.name = a.menu_name
JOIN tbluserprofilemaster p ON p.sub_institute_id = @sid AND LOWER(p.name) = LOWER(a.profile_name)
WHERE t.menu_id IS NOT NULL;

-- Right "Show Sidebar" panel: clicking the Placement dropdown (or opening any page under it) loads the rows whose
-- main_menu_id = Placement's menu id: tab per group, with the master pages inside.
SET @pl = (SELECT menu_id FROM tmp_menu WHERE name = 'Placement' AND lvl = 2);
DROP TEMPORARY TABLE IF EXISTS tmp_tab;
CREATE TEMPORARY TABLE tmp_tab (menu_name VARCHAR(150), tab_name VARCHAR(50), tab_sort INT, item_sort INT);
INSERT INTO tmp_tab VALUES
('Masters','Masters & Setup',1,1),
('Placement Seasons','Masters & Setup',1,2),
('Templates','Masters & Setup',1,3),
('Coordinators','Masters & Setup',1,4),
('Placement Policy','Policy & Eligibility',2,1),
('Eligibility Templates','Policy & Eligibility',2,2);

INSERT INTO rightside_menumaster (name, description, main_menu_id, parent_menu_id, tblmenu_master_id, icon, status, sort_order, sub_institute_id)
SELECT a.tab_name, a.tab_name, @pl, 0, NULL, 'setting', 1, MIN(a.tab_sort), CAST(@sid AS CHAR)
FROM tmp_tab a
WHERE @pl IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM rightside_menumaster r WHERE r.main_menu_id = @pl AND r.parent_menu_id = 0 AND r.name = a.tab_name)
GROUP BY a.tab_name;

INSERT INTO rightside_menumaster (name, description, main_menu_id, parent_menu_id, tblmenu_master_id, icon, status, sort_order, sub_institute_id)
SELECT t.name, t.name, @pl, tab.id, t.menu_id, 'setting', 1, a.item_sort, CAST(@sid AS CHAR)
FROM tmp_tab a
JOIN tmp_menu t ON t.name = a.menu_name AND t.lvl = 3
JOIN rightside_menumaster tab ON tab.main_menu_id = @pl AND tab.parent_menu_id = 0 AND tab.name = a.tab_name
WHERE t.menu_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM rightside_menumaster r WHERE r.main_menu_id = @pl AND r.tblmenu_master_id = t.menu_id);

UPDATE rightside_menumaster r
SET r.status = 1,
    r.sub_institute_id = IF(FIND_IN_SET(@sid, r.sub_institute_id), r.sub_institute_id, CONCAT_WS(',', NULLIF(r.sub_institute_id,''), @sid))
WHERE r.main_menu_id = @pl;

COMMIT;

-- check
SELECT m.id, m.level, m.name, m.menu_type, m.link, m.parent_menu_id, m.sub_institute_id, COUNT(g.id) AS rights_rows
FROM tblmenumaster m JOIN tmp_menu t ON t.menu_id = m.id
LEFT JOIN tblgroupwise_rights g ON g.menu_id = m.id AND g.sub_institute_id = @sid
GROUP BY m.id, m.level, m.name, m.menu_type, m.link, m.parent_menu_id, m.sub_institute_id, m.sort_order
ORDER BY m.level, m.parent_menu_id, m.sort_order;

SELECT r.id, r.main_menu_id, r.parent_menu_id, r.name, r.tblmenu_master_id, r.sub_institute_id
FROM rightside_menumaster r WHERE r.main_menu_id = @pl ORDER BY r.parent_menu_id, r.sort_order;
