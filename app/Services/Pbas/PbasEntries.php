<?php

namespace App\Services\Pbas;

/**
 * Definition of every PBAS data-entry screen (one per table). The original app had one near-identical PHP page
 * per table; here they share one controller and view and differ only by this definition: headings, form fields
 * (name, label, placeholder, options), the posted-name => column map and the list columns, all copied from the
 * original pages. Field `name` is what the form posts, `column` is the table column (defaults to name).
 *
 * Field types: year (academic-year dropdown from the ERP), text, number, select (options = [value, label] pairs),
 * radio (options = [value, label] pairs).
 */
class PbasEntries
{
    public const GROUPS = [
        'teaching' => 'Teaching Process',
        'departmental' => 'Departmental Activity',
        'institute' => 'Institute Activites',
        'society' => 'Contribution To Society',
    ];

    /** @return array<string, array> slug => definition */
    public static function all(): array
    {
        $year = ['name' => 'year', 'label' => 'Year', 'type' => 'year'];
        $reviewed = ['name' => 'reviewed', 'label' => 'Whether peer reviewed, impact factor, if any', 'type' => 'text', 'placeholder' => 'Enter Impact Factor if available, if not write NA'];
        $semesters = [];
        foreach (['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'] as $i => $label) {
            $semesters[] = [(string) ($i + 1), $label.' Semester'];
        }

        return [
            'lectures' => [
                'table' => 'pbas_lectures', 'group' => 'teaching', 'menu' => 'Lectures, Seminars, Tutorials',
                'crumb' => 'Teaching Process',
                'h1' => 'CATEGORY-1.1 TEACHING, LEARNING AND EVALUATION RELATED ACTIVITIES',
                'h5' => 'Lectures, Seminars, Tutorials, Practicals', 'note' => 'give semester-wise details, where necessary',
                'span' => 8, 'submit' => ['lec_detail', 'lec_detail'], 'delete' => 'delete_lecture', 'checkbox' => 'users',
                'fields' => [
                    $year,
                    ['name' => 'semester', 'label' => 'Semester', 'type' => 'select', 'options' => $semesters],
                    ['name' => 'subject', 'label' => 'Subject', 'type' => 'text', 'placeholder' => 'Enter Subject Name'],
                    ['name' => 'mode_teaching', 'label' => 'Mode of Teaching', 'type' => 'select', 'options' => [['Lecture', 'Lecture'], ['Lab', 'Lab'], ['Tutorial', 'Tutorial']]],
                    ['name' => 'class_alloted_weekly', 'label' => 'No of Classes per Week/ Total Alloted', 'type' => 'text', 'placeholder' => 'Provide Total Lectures during Semester as per Lesson Plan', 'int' => true],
                    ['name' => 'class_conducted_weekly', 'label' => 'No of Classes Conducted', 'type' => 'text', 'placeholder' => 'Provide Total No of Lectures Conducted', 'int' => true],
                    ['name' => 'percent_class_parc', 'label' => '% of classes / practical taken as per documented record', 'type' => 'text', 'placeholder' => 'Percentage of Classes/Lab Conducted', 'int' => true],
                    ['name' => 'gtu_percentage', 'label' => 'GTU Result Percentage', 'type' => 'text', 'placeholder' => 'Enter Result of GTU in %', 'int' => true],
                    ['name' => 'ssasit_percentage', 'label' => 'SSASIT Result Percentage', 'type' => 'text', 'placeholder' => 'Enter Result of SSASIT in %', 'int' => true],
                    ['name' => 'gturank', 'column' => 'gtu_rank', 'label' => 'GTU Rank', 'type' => 'number', 'id' => 'gturank', 'placeholder' => 'Enter Your Subject Rank in GTU ', 'int' => true],
                    ['name' => 'ssasit_rank', 'label' => 'Zonal Rank', 'type' => 'number', 'placeholder' => 'Enter Your Subject Rank', 'int' => true],
                ],
                'columns' => ['Year' => 'year', 'Sem' => 'semester', 'Subject' => 'subject', 'Mode of Teaching' => 'mode_teaching',
                    'No of classes per Week/ Total allotted' => 'class_alloted_weekly', 'No. of Classes Cond.' => 'class_conducted_weekly',
                    '% of classes / practical taken as per documented record' => 'percent_class_parc', 'GTU Result % ' => 'gtu_percentage',
                    'SSASIT Result %' => 'ssasit_percentage', 'GTU Result Rank ' => 'gtu_rank', 'SSASIT Rank' => 'ssasit_rank'],
            ],
            'materials' => [
                'table' => 'pbas_materials', 'group' => 'teaching', 'menu' => 'Reading/Instructional material Prepared',
                'crumb' => 'Teaching Process',
                'h1' => 'CATEGORY-1.2: TEACHING PROCESS Reading/Instructional material Prepared and additional knowledge resources provided.',
                'h5' => 'Reading/Instructional material Prepared and additional knowledge resources provided.',
                'note' => 'Provide individual entry per subject per material type',
                'span' => 6, 'submit' => ['material_detail', 'material_detail'], 'delete' => 'deleteMaterial', 'checkbox' => 'usersm',
                'fields' => [
                    ['name' => 'yearm', 'column' => 'year', 'label' => 'Year', 'type' => 'year'],
                    ['name' => 'subject', 'label' => 'Subject', 'type' => 'text', 'placeholder' => 'Enter Subject Name for Which you have Prepared Materials'],
                    ['name' => 'material_prepared', 'label' => 'Material Prepared', 'type' => 'radio', 'options' => [['yes', 'Yes'], ['no', 'No']]],
                    ['name' => 'material_type', 'label' => 'Type of Material', 'type' => 'radio', 'options' => [['PPT', 'PPT'], ['Lecture Notes', 'Lecture Notes'], ['Question Banks', 'Question Banks']]],
                    ['name' => 'quantity', 'label' => 'Prescribed Quantity', 'type' => 'text', 'placeholder' => 'Enter No. of PPT/Lect Notes/ Question Bank Prepared'],
                    ['name' => 'resources', 'label' => 'Additional Resources Provided', 'type' => 'text', 'placeholder' => 'Enter Additional Resources, If Not Enter -->NA<-- '],
                ],
                'columns' => ['Year' => 'year', 'Course/Paper' => 'subject', 'Prepared' => 'material_prepared', 'Material Type' => 'material_type',
                    'Prescribed/ Quantity' => 'quantity', 'Additional Resource Provided' => 'resources'],
            ],
            'papers' => [
                'table' => 'pbas_papers', 'group' => 'departmental', 'menu' => 'Published / Presented Papers',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES',
                'h5' => ' Published Papers in Journals and Paper Presented In Conference', 'note' => '',
                'span' => 7, 'submit' => ['paper_publication', 'development'], 'delete' => 'delete', 'checkbox' => 'usersdap',
                'fields' => [
                    $year,
                    ['name' => 'title', 'label' => 'Title with Page Nos', 'type' => 'text', 'placeholder' => 'Enter Title and Page No.'],
                    ['name' => 'published_in', 'label' => 'Paper Published in/Presented in', 'type' => 'select', 'options' => [
                        ['Scopus Index Journal', 'Scopus Index Journal'], ['UGC Approved Journal', 'UGC Approved Journal'],
                        ['Presentation in Conference/seminar', 'Presentation in Conference/seminar']]],
                    ['name' => 'journal_detail', 'label' => 'Journal/Conference Name', 'type' => 'text', 'placeholder' => 'Enter Journal Name'],
                    ['name' => 'isbn_no', 'label' => 'ISSN/ ISBN No.', 'type' => 'text', 'placeholder' => 'Enter ISSN/ISBN No. of Journal'],
                    $reviewed,
                    ['name' => 'co_author', 'label' => 'No. of co-authors', 'type' => 'text', 'placeholder' => 'Enter No. of Co-Author, put only number'],
                    ['name' => 'main_author', 'label' => 'Whether you are the main author', 'type' => 'text', 'placeholder' => 'Enter Yes if you are main author, otherwise No'],
                ],
                'columns' => ['Year' => 'year', ' Title' => 'title', ' Paper Published in/Presented in' => 'published_in', ' Journal/Conference Name' => 'journal_detail',
                    ' ISSN/ ISBN No.' => 'isbn_no', ' Whether peer reviewed, impact factor, if any' => 'reviewed', ' No. of co-authors' => 'co_author',
                    ' Whether you are the main author' => 'main_author'],
            ],
            'expert-lectures' => [
                'table' => 'pbas_expert_lectures', 'group' => 'departmental', 'menu' => 'Worked as an Expert',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES',
                'h5' => ' Expert Lecture Delivered', 'note' => '',
                'span' => 6, 'submit' => ['expert_lecture', 'expert_lecture'], 'delete' => 'delete', 'checkbox' => 'usersdaexpert',
                'fields' => [
                    $year,
                    ['name' => 'topic', 'label' => 'Topic Of Lecture', 'type' => 'text', 'placeholder' => 'Enter Title of lecture'],
                    ['name' => 'level', 'label' => 'Level', 'type' => 'select', 'options' => [['State Level/National Level', 'State Level/National Level'], ['SSASIT Level', 'SSASIT Level']]],
                    ['name' => 'date', 'label' => 'Date', 'type' => 'text', 'placeholder' => 'Enter Date of Lecture'],
                ],
                'columns' => ['Year' => 'year', ' Topic' => 'topic', ' Level' => 'level', ' Date' => 'date'],
            ],
            'books' => [
                'table' => 'pbas_books', 'group' => 'departmental', 'menu' => 'Articles / Chapters  published in Books',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES',
                'h5' => ' Articles / Chapters published in Books', 'note' => '',
                'span' => 6, 'submit' => ['book_author', 'development'], 'delete' => 'delete', 'checkbox' => 'usersdab',
                'fields' => [
                    $year,
                    ['name' => 'publication_type', 'label' => 'Publication Type', 'type' => 'select', 'options' => [
                        ['International Publication', 'International Publication'], ['National Publication', 'National Publication'], ['Local Publication', 'Local Publication']]],
                    ['name' => 'book_title', 'label' => 'Title of Book', 'type' => 'text', 'placeholder' => 'Enter Title and Page No.'],
                    ['name' => 'isbn_no', 'label' => 'ISSN/ ISBN No.', 'type' => 'text', 'placeholder' => 'Enter ISSN/ISBN No. of Journal'],
                    ['name' => 'author_type', 'label' => 'Are You Soul Author/ Co-Author?', 'type' => 'select', 'options' => [['Soul Author', 'Soul Author'], ['Co-Author', 'Co-Author']]],
                ],
                'columns' => ['Year' => 'year', ' Book Title' => 'book_title', ' Publication Type' => 'publication_type', ' ISBN No.' => 'isbn_no', ' Author Type' => 'author_type'],
            ],
            'research-projects' => [
                'table' => 'pbas_research_projects', 'group' => 'departmental', 'menu' => 'Grant Received for Research Projects',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES',
                'h5' => ' Research Projects and Consultancies', 'note' => '',
                'span' => 6, 'submit' => ['research_project', 'expert_lecture'], 'delete' => 'delete', 'checkbox' => 'usersdaresearchp',
                'fields' => [
                    $year,
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'placeholder' => 'Enter Title of project'],
                    ['name' => 'agency', 'label' => 'Agency Name', 'type' => 'text', 'placeholder' => 'Enter Sponsored Agency Name'],
                    ['name' => 'period', 'label' => 'Period', 'type' => 'text', 'placeholder' => 'Enter Duration '],
                    ['name' => 'amount', 'label' => 'Sponsered Amount in Lacs ', 'type' => 'select', 'options' => [['1', ' Up to 1 Lac'], ['2', 'Greater Than 1 Lac']]],
                ],
                'columns' => ['Year' => 'year', ' Title' => 'title', ' Agency' => 'agency', ' Period' => 'period', ' Amount' => 'amount'],
            ],
            'research-individuals' => [
                'table' => 'pbas_research_individuals', 'group' => 'departmental', 'menu' => 'Research Work',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES',
                'h5' => ' Research Work', 'note' => '',
                'span' => 6, 'submit' => ['research_individual', 'expert_ind'], 'delete' => 'delete', 'checkbox' => 'usersdaresearchi',
                'fields' => [
                    $year,
                    ['name' => 'enrollment_number', 'label' => 'Enrollment Number', 'type' => 'text', 'placeholder' => 'Enter Your Enrollment Number'],
                    ['name' => 'enrollment_year', 'label' => 'Year Of Enrollment of Course', 'type' => 'text', 'placeholder' => ' Enter year in 4 digits i.e. 2018'],
                    ['name' => 'thesis_submitted', 'label' => 'Is Thesis Submitted?', 'type' => 'text', 'placeholder' => 'Enter Yes/No'],
                    ['name' => 'degree_awarded', 'label' => 'Is Degree Awarded?', 'type' => 'text', 'placeholder' => 'Enter Yes/No'],
                ],
                'columns' => ['Year' => 'year', ' Enrollment Number' => 'enrollment_number', ' Enrollment Year' => 'enrollment_year', ' Thesis Submitted?' => 'thesis_submitted', ' Degree Awarded?' => 'degree_awarded'],
            ],
            'project-guides' => [
                'table' => 'pbas_project_guides', 'group' => 'departmental', 'menu' => 'Project Guided (UG/PG/Ph.D)',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES',
                'h5' => ' Project Guided (UG/PG/Ph.D)', 'note' => '',
                'span' => 6, 'submit' => ['project_guide', 'project_guide'], 'delete' => 'delete', 'checkbox' => 'usersdaproject_guide',
                'fields' => [
                    $year,
                    ['name' => 'project_type', 'label' => 'Type Of Project', 'type' => 'select', 'options' => [
                        ['UG Final Year Project', 'UG Final Year Project'], ['DE Project', 'DE Project'], ['PG Project', 'PG Project'], ['Ph.D project', 'Ph.D project']]],
                    ['name' => 'number', 'label' => 'Number Of Projects Guided', 'type' => 'number', 'placeholder' => 'Enter numeric data only'],
                    ['name' => 'guide_type', 'label' => 'Guidance Type', 'type' => 'select', 'options' => [['Main Guide', 'Main/Core Guide'], ['Co-Guide', 'Co-Guide']]],
                ],
                'columns' => ['Year' => 'year', 'Project Type' => 'project_type', 'Number of Projects' => 'number', 'Guidance Type' => 'guide_type'],
            ],
            'sttps' => [
                'table' => 'pbas_sttps', 'group' => 'departmental', 'menu' => 'STTP/FDP Attended',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES: STTP/FDP Attended',
                'h5' => ' STTP/FDP Attended', 'note' => '',
                'span' => 6, 'submit' => ['sttp', 'sttp'], 'delete' => 'delete', 'checkbox' => 'usersda_sttp',
                'fields' => [
                    $year,
                    ['name' => 'program', 'label' => 'Training Program Type', 'type' => 'select', 'options' => [['STTP', 'STTP'], ['FDP', 'FDP']]],
                    ['name' => 'title', 'label' => 'Title of STTP/FDP', 'type' => 'text', 'placeholder' => 'Enter Title'],
                    ['name' => 'organizer', 'label' => 'STTP/FDP Organized by', 'type' => 'text', 'placeholder' => 'Enter Organization Name'],
                    ['name' => 'date', 'label' => 'Date', 'type' => 'text', 'placeholder' => 'Enter Date '],
                    ['name' => 'day_number', 'label' => 'No. of Days', 'type' => 'number', 'placeholder' => 'Enter Number of Days in Numeric Form'],
                ],
                'columns' => ['Year' => 'year', 'Program Type' => 'program', 'Title' => 'title', 'Organizer' => 'organizer', 'Date' => 'date', 'No of Days' => 'day_number'],
            ],
            'awards' => [
                'table' => 'pbas_awards', 'group' => 'departmental', 'menu' => 'Award/ Recognition',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES',
                'h5' => ' Award/Recognition for Outstanding Performance', 'note' => '',
                'span' => 6, 'submit' => ['award', 'award'], 'delete' => 'delete', 'checkbox' => 'usersdaaward',
                'fields' => [
                    $year,
                    ['name' => 'name', 'label' => 'Title of Award', 'type' => 'text', 'placeholder' => 'Enter Title'],
                    ['name' => 'givenby', 'label' => 'Award Given By', 'type' => 'text', 'placeholder' => 'Enter Organization Name'],
                    ['name' => 'date', 'label' => 'Date', 'type' => 'text', 'placeholder' => 'Enter Date of Achievement'],
                ],
                'columns' => ['Year' => 'year', 'Title' => 'name', 'Organization' => 'givenby', 'Date' => 'date'],
            ],
            'admin-duties' => [
                'table' => 'pbas_admin_duties', 'group' => 'institute',
                'menu' => 'Contribution to Corporate Life and Management of the Institution: HoD/Dean/Committee Membership/Class Coordinator/Lab In charge',
                'crumb' => 'Institute Activities', 'h1' => 'CATEGORY-INSTITUTE ACTIVITIES',
                'h5' => ' Administaration Duties', 'note' => '',
                'span' => 6, 'submit' => ['admin', 'development'], 'delete' => 'delete', 'checkbox' => 'usersiaa',
                'fields' => [
                    $year,
                    ['name' => 'post', 'label' => 'Post/Designation', 'type' => 'select', 'options' => [
                        ['HOD/Dean', 'HOD/Dean'], ['Co-Dean', 'Co-Dean'], ['Committee Member', 'Committee Member'],
                        ['Class coordinator', 'Class Coordinator'], ['Lab Incharge', 'Lab Incharge']]],
                    ['name' => 'com_name', 'label' => 'Committee Name/Branch Name (in case of HOD/Class coordinator/Lab Incharge)', 'type' => 'text', 'placeholder' => 'Enter committee name/Branch name'],
                ],
                'columns' => ['Year' => 'year', 'Post' => 'post', 'Committee Name/Branch Name' => 'com_name'],
            ],
            'events' => [
                'table' => 'pbas_events', 'group' => 'institute',
                'menu' => 'Extension Co-curricular & field based activities: Event Coordination at GTU/State/SSASIT',
                'crumb' => 'Institute Activities', 'h1' => 'CATEGORY-INSTITUTE ACTIVITIES: EVENT ORGANIZED',
                'h5' => ' Event Coordination', 'note' => '',
                'span' => 6, 'submit' => ['event', 'event'], 'delete' => 'delete', 'checkbox' => 'usersiae',
                'fields' => [
                    $year,
                    ['name' => 'event_name', 'label' => 'Event Name', 'type' => 'text', 'placeholder' => 'Enter Event Name'],
                    ['name' => 'level', 'label' => 'Event Type', 'type' => 'select', 'options' => [
                        ['GTU/State Level/National Level', 'GTU/State Level/National level'], ['SSASIT Level', 'SSASIT Level'], ['Department Level', 'Department Level']]],
                    ['name' => 'event_post', 'column' => 'post', 'label' => 'Post/Designation', 'type' => 'select', 'options' => [['Coordinator', 'Coordinator'], ['Member', 'Member']]],
                ],
                'columns' => ['Year' => 'year', 'Event Name' => 'event_name', 'Event Level/Type' => 'level', 'Post' => 'post'],
            ],
            'industrial-visits' => [
                'table' => 'pbas_industrial_visits', 'group' => 'society', 'menu' => 'Industrial Visit/Training',
                'crumb' => 'Teaching Process', 'h1' => 'CATEGORY-Contibution To Society',
                'h5' => ' Industrial Visit/training', 'note' => '',
                'span' => 6, 'submit' => ['development', 'development'], 'delete' => 'delete', 'checkbox' => 'userscsi',
                'fields' => [
                    $year,
                    ['name' => 'activity', 'label' => 'Industrial Visit/training', 'type' => 'select', 'options' => [
                        ['Industrial Visit/Training', 'Industrial Visit/Training'], ['Industrial Visit with students ', 'Industrial Visit With Students']]],
                    ['name' => 'name', 'label' => 'Name of Industry', 'type' => 'text', 'placeholder' => 'Provide Industry Name'],
                    ['name' => 'responsibilities', 'label' => 'Number Of days of Visit/Training', 'type' => 'number', 'placeholder' => 'Provide Total No of Industrial Visits'],
                ],
                'columns' => ['Year' => 'year', 'Type Of Visit' => 'activity', 'Name of Industry' => 'name', 'Number of Visits during year' => 'responsibilities'],
            ],
            'memberships' => [
                'table' => 'pbas_memberships', 'group' => 'society', 'menu' => 'Professional/Technical Society Membership',
                'crumb' => 'Contribution To Society', 'h1' => 'CATEGORY-CONTRIBUTION TO SOCIETY',
                'h5' => ' Professional Membership', 'note' => '',
                'span' => 6, 'submit' => ['membership', 'development'], 'delete' => 'delete', 'checkbox' => 'userscsmem',
                'fields' => [
                    $year,
                    ['name' => 'professionalbody_name', 'label' => 'professional Body Name', 'type' => 'text', 'placeholder' => 'Provide Technical Society Name'],
                    ['name' => 'membership_duration', 'label' => 'Membership Duration', 'type' => 'text', 'placeholder' => 'Provide Membership Duration'],
                    ['name' => 'membership_id', 'label' => 'Membership Id.', 'type' => 'text', 'placeholder' => 'Provide Membership id'],
                ],
                'columns' => ['Year' => 'year', 'Professional Body Name' => 'professionalbody_name', 'Membership Duration' => 'membership_duration', 'Membership Id' => 'membership_id'],
            ],
            'community-activities' => [
                'table' => 'pbas_community_activities', 'group' => 'society', 'menu' => 'Participation/ Organization of Community Development Activities',
                'crumb' => 'Contribution To Society', 'h1' => 'CATEGORY-CONTRIBUTION TO SOCIETY',
                'h5' => ' Involvement In Community development Activities', 'note' => '',
                'span' => 6, 'submit' => ['other', 'other'], 'delete' => 'delete', 'checkbox' => 'userscsother',
                'fields' => [
                    $year,
                    ['name' => 'name', 'label' => 'Select Name Of Activity', 'type' => 'select', 'options' => [
                        ['Blood Donation Camp', 'Blood Donation Camp'], ['Yoga class', 'Yoga Class'], ['Vishwakarma Yojana', 'Vishwakarma Yojana'],
                        ['Tree Plantation', 'Tree Plantation'], ['Yoga class', 'Yoga Class']]],
                    ['name' => 'worked_as', 'label' => 'Participated as', 'type' => 'select', 'options' => [
                        ['Blood Donation Camp organizer', 'Blood Donation Camp Organizer'], ['Yoga class', ' member/organizer/donor']]],
                    ['name' => 'date', 'label' => 'date', 'type' => 'text', 'placeholder' => 'enter date of activity'],
                ],
                'columns' => ['Year' => 'year', 'Name of Event' => 'name', 'Worked As' => 'worked_as', 'Date' => 'date'],
            ],
            // Not in the original sidebar (commented out there) but still reachable by URL and read by the API form.
            'exam-duties' => [
                'table' => 'pbas_exam_duties', 'group' => null, 'menu' => 'Exam duties',
                'crumb' => 'Institute Activities', 'h1' => 'CATEGORY-INSTITUTE ACTIVITIES: EVENT ORGANIZED',
                'h5' => ' Event Coordination', 'note' => '',
                'span' => 6, 'submit' => ['exam', 'exam'], 'delete' => 'delete', 'checkbox' => 'usersiaexam',
                'fields' => [
                    $year,
                    ['name' => 'exam_duty_type', 'label' => 'Type of Examination Duties', 'type' => 'select', 'options' => [
                        ['Question Paper Setting at SSASIT Level', 'Question Paper Setting at SSASIT Level'], ['Question Paper Setting at GTU Level', 'Question Paper Setting at GTU Level'],
                        ['Answer book assessed at GTU/SSASIT Level', 'Answer book assessed at GTU/SSASIT Level'],
                        ['Worked as external/Internal Practical Examiner', 'Worked as external/Internal Practical Examiner'],
                        ['Invigilation duty/Supervisor', 'Invigilation duty/Supervisor'], ['Observer/Squad Duty', 'Observer/Squad Duty']]],
                    ['name' => 'number_of_duties', 'label' => 'No of Duties Assigned', 'type' => 'number', 'placeholder' => 'Enter Total No. of duties assigned'],
                    ['name' => 'percent_carried', 'label' => 'Extent to which carried out (%)', 'type' => 'text', 'placeholder' => 'Enter %, Provide 00 in case of 0%', 'int' => true],
                ],
                'columns' => ['Year' => 'year', 'Exam Duty Type' => 'exam_duty_type', 'Number Of Duties' => 'number_of_duties', 'Percentage Carried Out' => 'percent_carried'],
            ],
            'industrial-trainings' => [
                'table' => 'pbas_industrial_trainings', 'group' => null, 'menu' => 'Industrial Training',
                'crumb' => 'Departmental Activities', 'h1' => 'CATEGORY-DEPARTMENTAL ACTIVITIES: Industrial Training',
                'h5' => ' Industrial Training', 'note' => '',
                'span' => 6, 'submit' => ['ind_training', 'ind_t'], 'delete' => 'delete', 'checkbox' => 'usersda_ind_training',
                'fields' => [
                    $year,
                    ['name' => 'name', 'label' => 'Name of Industry', 'type' => 'text', 'placeholder' => 'Enter Name'],
                    ['name' => 'date', 'label' => 'Date (Start Date - End Date)', 'type' => 'text', 'placeholder' => 'Enter Date'],
                    ['name' => 'day_number', 'label' => 'Number of Days', 'type' => 'number', 'placeholder' => 'Enter Number of Days '],
                ],
                'columns' => ['Year' => 'year', 'Name of Industry' => 'name', 'Date' => 'date', 'No of Days' => 'day_number'],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /** Slugs shown in the menu, grouped, in sidebar order. */
    public static function menuGroups(): array
    {
        $out = [];
        foreach (self::all() as $slug => $def) {
            if ($def['group']) {
                $out[$def['group']][$slug] = $def['menu'];
            }
        }

        return $out;
    }
}
