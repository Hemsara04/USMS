import os
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

def create_document():
    doc = Document()

    # Page Margins (Standard 1 inch / 2.54 cm)
    for section in doc.sections:
        section.top_margin = Inches(0.8)
        section.bottom_margin = Inches(0.8)
        section.left_margin = Inches(0.9)
        section.right_margin = Inches(0.9)

    # Palette
    COLOR_PRIMARY = RGBColor(7, 22, 45)      # Deep Navy #07162D
    COLOR_TEAL = RGBColor(8, 127, 128)       # Teal #087F80
    COLOR_MUTED = RGBColor(100, 116, 139)    # Slate #64748B
    COLOR_DARK = RGBColor(30, 41, 59)        # Body Dark #1E293B

    # Helper: Set paragraph font
    def style_para(p, space_before=0, space_after=6, line_spacing=1.15):
        p.paragraph_format.space_before = Pt(space_before)
        p.paragraph_format.space_after = Pt(space_after)
        p.paragraph_format.line_spacing = line_spacing

    # Helper: Set table cell shading
    def set_cell_background(cell, hex_color):
        shading = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
        cell._tc.get_or_add_tcPr().append(shading)

    # Helper: Set cell margins
    def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
        tcPr = cell._tc.get_or_add_tcPr()
        tcMar = OxmlElement('w:tcMar')
        for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
            node = OxmlElement(f'w:{m}')
            node.set(qn('w:w'), str(val))
            node.set(qn('w:type'), 'dxa')
            tcMar.append(node)
        tcPr.append(tcMar)

    # ------------------ HEADER ------------------
    p_inst = doc.add_paragraph()
    style_para(p_inst, 0, 2)
    r_inst = p_inst.add_run("UNIVERSITY OF COLOMBO SCHOOL OF COMPUTING (UCSC)")
    r_inst.font.name = "Arial"
    r_inst.font.size = Pt(10)
    r_inst.font.bold = True
    r_inst.font.color.rgb = COLOR_PRIMARY

    p_course = doc.add_paragraph()
    style_para(p_course, 0, 4)
    r_course = p_course.add_run("SCS2301 / IS2201: Software Engineering Project • Academic Year 2025/2026")
    r_course.font.name = "Arial"
    r_course.font.size = Pt(9.5)
    r_course.font.color.rgb = COLOR_TEAL

    p_title = doc.add_paragraph()
    style_para(p_title, 2, 4)
    r_title = p_title.add_run("Interim Action Plan Document")
    r_title.font.name = "Arial"
    r_title.font.size = Pt(18)
    r_title.font.bold = True
    r_title.font.color.rgb = COLOR_PRIMARY

    p_proj = doc.add_paragraph()
    style_para(p_proj, 0, 10)
    r_proj = p_proj.add_run("Project: University Sports Management System (USMS) | ")
    r_proj.font.name = "Arial"
    r_proj.font.size = Pt(10.5)
    r_proj.font.bold = True
    r_proj.font.color.rgb = COLOR_DARK

    r_grp = p_proj.add_run("Group: IS03 | ")
    r_grp.font.name = "Arial"
    r_grp.font.size = Pt(10.5)
    r_grp.font.bold = True
    r_grp.font.color.rgb = COLOR_TEAL

    r_stat = p_proj.add_run("Proposal Outcome: ACCEPTED")
    r_stat.font.name = "Arial"
    r_stat.font.size = Pt(10.5)
    r_stat.font.bold = True
    r_stat.font.color.rgb = RGBColor(21, 128, 61)

    # ------------------ SECTION 1 ------------------
    h1 = doc.add_paragraph()
    style_para(h1, 10, 4)
    r_h1 = h1.add_run("1. Executive Summary")
    r_h1.font.name = "Arial"
    r_h1.font.size = Pt(13)
    r_h1.font.bold = True
    r_h1.font.color.rgb = COLOR_PRIMARY

    p_intro = doc.add_paragraph()
    style_para(p_intro, 0, 6)
    r_intro = p_intro.add_run(
        "During the proposal presentation and defense for Group IS03, the evaluation panel accepted our project proposal. "
        "The overall outcome was recorded as ACCEPT. The panel provided constructive feedback on five specific areas: "
        "handling student verification without live database access, treating security as an explicit functional requirement, "
        "ensuring multi-sport support across all university sports, removing automated medical locks, and revising our rejected design diagrams. "
        "This document clearly outlines the feedback received, explains what it means, and details the actions we have taken or plan to take."
    )
    r_intro.font.name = "Arial"
    r_intro.font.size = Pt(10)
    r_intro.font.color.rgb = COLOR_DARK

    # ------------------ SECTION 2: SUMMARY TABLE ------------------
    h2 = doc.add_paragraph()
    style_para(h2, 12, 4)
    r_h2 = h2.add_run("2. Summary of Feedback Received")
    r_h2.font.name = "Arial"
    r_h2.font.size = Pt(13)
    r_h2.font.bold = True
    r_h2.font.color.rgb = COLOR_PRIMARY

    # Feedback Table
    tbl = doc.add_table(rows=1, cols=3)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    tbl.autofit = False

    col_widths = [Inches(1.8), Inches(3.2), Inches(1.7)]
    for i, w in enumerate(col_widths):
        tbl.columns[i].width = w

    # Header Row
    hdr_cells = tbl.rows[0].cells
    headers = ["Evaluation Area", "Panel Feedback / Comment", "Current Status"]
    for i, text in enumerate(headers):
        hdr_cells[i].width = col_widths[i]
        p = hdr_cells[i].paragraphs[0]
        style_para(p, 2, 2)
        r = p.add_run(text)
        r.font.name = "Arial"
        r.font.size = Pt(9.5)
        r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_background(hdr_cells[i], "07162D")
        set_cell_margins(hdr_cells[i], top=120, bottom=120, left=150, right=150)

    # Feedback Data
    feedback_data = [
        ("1. Student DB Verification", "You can't cross-check with the student database practically. So, it is okay to assume that the verification against real university students can be done when such information is available.", "Addressed (Internal Verification Queue)"),
        ("2. Security Classification", "Security is a functional requirement.", "Addressed (6 Security Functional Requirements)"),
        ("3. Sports Coverage", "You mentioned that you will be covering all the sports.", "Addressed (Dynamic Multi-Sport Catalog)"),
        ("4. Medical Lock Release", "You don't have to automate medical lock release as it may result in unnecessary complications.", "Addressed (Manual Clinical Clearance)"),
        ("5. Design Diagrams (Reject)", "Standardize component diagrams and ensure UI/component consistency across team branches on GitHub.", "Rectified (UML Component Diagram & Shared UI)"),
    ]

    for area, comment, status in feedback_data:
        row = tbl.add_row()
        cells = row.cells
        cells[0].width = col_widths[0]
        cells[1].width = col_widths[1]
        cells[2].width = col_widths[2]

        p0 = cells[0].paragraphs[0]
        style_para(p0, 2, 2)
        r0 = p0.add_run(area)
        r0.font.name = "Arial"
        r0.font.size = Pt(9)
        r0.font.bold = True
        r0.font.color.rgb = COLOR_DARK

        p1 = cells[1].paragraphs[0]
        style_para(p1, 2, 2)
        r1 = p1.add_run(comment)
        r1.font.name = "Arial"
        r1.font.size = Pt(8.5)
        r1.font.italic = True
        r1.font.color.rgb = COLOR_DARK

        p2 = cells[2].paragraphs[0]
        style_para(p2, 2, 2)
        r2 = p2.add_run(status)
        r2.font.name = "Arial"
        r2.font.size = Pt(8.5)
        r2.font.bold = True
        r2.font.color.rgb = COLOR_TEAL

        for c in cells:
            set_cell_background(c, "F8FAFC")
            set_cell_margins(c, top=80, bottom=80, left=120, right=120)

    # ------------------ SECTION 3: DETAILED RESOLUTIONS ------------------
    h3 = doc.add_paragraph()
    style_para(h3, 14, 4)
    r_h3 = h3.add_run("3. Detailed Feedback & Actions Taken")
    r_h3.font.name = "Arial"
    r_h3.font.size = Pt(13)
    r_h3.font.bold = True
    r_h3.font.color.rgb = COLOR_PRIMARY

    items = [
        {
            "num": "3.1",
            "title": "Student Database Verification (Assumption Modeling)",
            "comment": "“You can't cross-check with the student database practically. So, it is okay to assume that the verification against real university students can be done when such information is available.”",
            "explanation": "What this means: As 2nd-year undergraduate students, we do not have access to live University of Colombo student administration APIs due to privacy regulations. The panel acknowledged this practical reality and permitted us to treat external university database verification as an assumption.",
            "actions": [
                "Built an Internal Verification Queue in the Admin portal (views/admin_user_management.php).",
                "When a student registers as an athlete, they submit their university Index Number and Faculty. Their profile is placed into a 'Pending' verification queue.",
                "An Admin or TIC officer reviews the registration details and clicks 'Approve' or 'Reject'.",
                "Prepared an extensible service adapter interface so that when official institutional API access is granted, the live connection can be plugged in without changing the core codebase."
            ]
        },
        {
            "num": "3.2",
            "title": "Security as a Functional Requirement",
            "comment": "“Security is a functional requirement.”",
            "explanation": "What this means: The evaluators highlighted that security must not be treated as a simple non-functional bullet point. It must be built as working functional features that can be tested and demonstrated.",
            "actions": [
                "BCRYPT Password Hashing: All user passwords are encrypted using PHP password_hash(..., PASSWORD_BCRYPT) and verified using password_verify() in models/User.php and controllers/AuthController.php.",
                "Role-Based Access Control (RBAC): A central middleware guard (config/auth_guard.php) strictly enforces permissions for 8 roles (Admin, Coach, TIC, Medical Officer, President, Athlete, etc.). Unauthorized page access is blocked automatically.",
                "SQL Injection Prevention: All database queries across all models use PDO Parameterized Prepared Statements (PDO::ATTR_EMULATE_PREPARES => false).",
                "XSS Protection: All dynamic output is escaped with htmlspecialchars(..., ENT_QUOTES, 'UTF-8').",
                "Audit Logging: Created views/admin_audit_logs.php to record sensitive administrative actions (user edits, deletions, role updates)."
            ]
        },
        {
            "num": "3.3",
            "title": "Sports Coverage (Supporting All University Sports)",
            "comment": "“You mentioned that you will be covering all the sports.”",
            "explanation": "What this means: In the proposal, our group committed to supporting all university sports, not just one sport. The panel reminded us to ensure our architecture dynamically accommodates multiple sports.",
            "actions": [
                "Designed a centralized sports database table that supports all 24+ recognized University of Colombo sports (Cricket, Badminton, Football, Basketball, Swimming, Track & Field, Rugby, Tennis, etc.).",
                "All operational tables—training_sessions, fixtures, match_squads, equipment, and injuries—are relationally mapped to sport_id.",
                "Built an administrative Sports Catalog view (views/admin_sports_catalog.php) allowing administrators to add, view, or configure any sport dynamically without modifying any code."
            ]
        },
        {
            "num": "3.4",
            "title": "Medical Lock Release (Manual Clinical Sign-Off)",
            "comment": "“You don't have to automate medical lock release as it may result in unnecessary complications.”",
            "explanation": "What this means: If an athlete gets injured and the system automatically unlocks them after 14 days, an unfit player could compete and suffer severe injuries. The panel emphasized that medical clearance must strictly be a doctor's decision.",
            "actions": [
                "Completely removed any automated or time-based medical lock release.",
                "Made medical clearance 100% manual: only an authorized Medical Officer can certify that an athlete is fit to compete (views/med_clearance.php and models/Medical.php).",
                "The Medical Officer must clinically examine the athlete, enter medical assessment notes, and manually submit the clearance certification."
            ]
        },
        {
            "num": "3.5",
            "title": "Design Diagrams (Rectifying Rejection & Component Consistency)",
            "comment": "Design Diagrams were marked as 'Reject' in the evaluation spreadsheet.",
            "explanation": "What this means: The early design diagrams lacked standard UML formatting, had architectural discrepancies, and showed inconsistent UI components across different team members' branches.",
            "actions": [
                "Standardized our UML Component Diagram into a clean 3-tier MVC architecture (Dynamic Views -> Access Guard Middleware -> Controllers -> Models -> MySQL Database).",
                "Unified the UI Design System across all roles on GitHub (Hemsara04/USMS): established consistent colors (Deep Navy #07162D, Athletic Teal #087F80), shared navigation sidebars, standardized metric cards, and clean data tables.",
                "Currently updating the Activity and Sequence diagrams to match the exact implemented code before the final presentation."
            ]
        }
    ]

    for item in items:
        p_sub = doc.add_paragraph()
        style_para(p_sub, 8, 2)
        r_sub = p_sub.add_run(f"{item['num']} {item['title']}")
        r_sub.font.name = "Arial"
        r_sub.font.size = Pt(11)
        r_sub.font.bold = True
        r_sub.font.color.rgb = COLOR_PRIMARY

        # Panel feedback quote box
        p_q = doc.add_paragraph()
        style_para(p_q, 1, 3)
        r_q = p_q.add_run(f"Feedback Received: {item['comment']}")
        r_q.font.name = "Arial"
        r_q.font.size = Pt(9)
        r_q.font.italic = True
        r_q.font.color.rgb = COLOR_TEAL

        # Explanation
        p_exp = doc.add_paragraph()
        style_para(p_exp, 1, 3)
        r_exp = p_exp.add_run(item['explanation'])
        r_exp.font.name = "Arial"
        r_exp.font.size = Pt(9.5)
        r_exp.font.color.rgb = COLOR_DARK

        # Actions bullet list
        p_act_lbl = doc.add_paragraph()
        style_para(p_act_lbl, 2, 2)
        r_act_lbl = p_act_lbl.add_run("Action Taken / Plan:")
        r_act_lbl.font.name = "Arial"
        r_act_lbl.font.size = Pt(9.5)
        r_act_lbl.font.bold = True
        r_act_lbl.font.color.rgb = COLOR_PRIMARY

        for act in item['actions']:
            p_bullet = doc.add_paragraph(style='List Bullet')
            style_para(p_bullet, 1, 2)
            r_b = p_bullet.add_run(act)
            r_b.font.name = "Arial"
            r_b.font.size = Pt(9)
            r_b.font.color.rgb = COLOR_DARK

    # ------------------ SECTION 4: ROADMAP & TIMELINE ------------------
    h4 = doc.add_paragraph()
    style_para(h4, 14, 4)
    r_h4 = h4.add_run("4. Action Plan Timeline & Milestones")
    r_h4.font.name = "Arial"
    r_h4.font.size = Pt(13)
    r_h4.font.bold = True
    r_h4.font.color.rgb = COLOR_PRIMARY

    p_time_desc = doc.add_paragraph()
    style_para(p_time_desc, 0, 6)
    r_time_desc = p_time_desc.add_run(
        "To ensure all components are completed on time for the final defense, we have organized our work into clear milestone sprints:"
    )
    r_time_desc.font.name = "Arial"
    r_time_desc.font.size = Pt(9.5)
    r_time_desc.font.color.rgb = COLOR_DARK

    tbl_sprints = doc.add_table(rows=1, cols=3)
    tbl_sprints.alignment = WD_TABLE_ALIGNMENT.CENTER
    tbl_sprints.autofit = False

    spr_widths = [Inches(1.5), Inches(3.8), Inches(1.4)]
    for i, w in enumerate(spr_widths):
        tbl_sprints.columns[i].width = w

    hdr_s = tbl_sprints.rows[0].cells
    hdr_s_titles = ["Sprint / Milestone", "Key Tasks & Deliverables", "Status"]
    for i, text in enumerate(hdr_s_titles):
        hdr_s[i].width = spr_widths[i]
        p = hdr_s[i].paragraphs[0]
        style_para(p, 2, 2)
        r = p.add_run(text)
        r.font.name = "Arial"
        r.font.size = Pt(9)
        r.font.bold = True
        r.font.color.rgb = RGBColor(255, 255, 255)
        set_cell_background(hdr_s[i], "07162D")
        set_cell_margins(hdr_s[i], top=100, bottom=100, left=120, right=120)

    sprints_data = [
        ("Sprint 1 (Baseline)", "Unified login portal, BCRYPT password hashing, RBAC middleware, MySQL schema setup.", "Completed"),
        ("Sprint 2 (Interim)", "Admin portal User Management CRUD, verification queue, dynamic dashboard statistics.", "Completed"),
        ("Sprint 3 (Current)", "TIC Equipment & Stock Inventory CRUD, checkouts, Medical Officer manual clinical clearance.", "Completed"),
        ("Sprint 4 (Diagrams)", "Overhaul UML Component, Activity, and Sequence diagrams to match the live MVC code.", "In Progress"),
        ("Sprint 5 (Quality Assurance)", "UI consistency audit across student/coach portals, responsive testing, edge-case validation.", "Planned"),
        ("Final Phase", "Final documentation, deployment preparation, video demonstration, and system defense.", "Scheduled"),
    ]

    for spr, desc, stat in sprints_data:
        row = tbl_sprints.add_row()
        cells = row.cells
        cells[0].width = spr_widths[0]
        cells[1].width = spr_widths[1]
        cells[2].width = spr_widths[2]

        p0 = cells[0].paragraphs[0]
        style_para(p0, 2, 2)
        r0 = p0.add_run(spr)
        r0.font.name = "Arial"
        r0.font.size = Pt(8.5)
        r0.font.bold = True
        r0.font.color.rgb = COLOR_DARK

        p1 = cells[1].paragraphs[0]
        style_para(p1, 2, 2)
        r1 = p1.add_run(desc)
        r1.font.name = "Arial"
        r1.font.size = Pt(8.5)
        r1.font.color.rgb = COLOR_DARK

        p2 = cells[2].paragraphs[0]
        style_para(p2, 2, 2)
        r2 = p2.add_run(stat)
        r2.font.name = "Arial"
        r2.font.size = Pt(8.5)
        r2.font.bold = True
        r2.font.color.rgb = COLOR_TEAL if "Completed" in stat or "In Progress" in stat else COLOR_MUTED

        for c in cells:
            set_cell_background(c, "F8FAFC")
            set_cell_margins(c, top=60, bottom=60, left=100, right=100)

    # ------------------ SECTION 5: TEAM CONTRIBUTIONS ------------------
    h5 = doc.add_paragraph()
    style_para(h5, 14, 4)
    r_h5 = h5.add_run("5. Team Members & Contributions")
    r_h5.font.name = "Arial"
    r_h5.font.size = Pt(13)
    r_h5.font.bold = True
    r_h5.font.color.rgb = COLOR_PRIMARY

    p_git = doc.add_paragraph()
    style_para(p_git, 0, 8)
    r_git = p_git.add_run(
        "All features are tracked and integrated on our shared GitHub repository: Hemsara04/USMS (main branch)."
    )
    r_git.font.name = "Arial"
    r_git.font.size = Pt(9.5)
    r_git.font.color.rgb = COLOR_DARK

    team = [
        ("Hasitha Hemsara", "Index: 22020294 / IS", "Role: Backend & Security Lead (Authentication, RBAC, Admin & TIC CRUD)"),
        ("Sandaruwan Bandara", "Index: 22020308 / IS", "Role: Coach & Match Coordinator (Training Sessions, Squad Formulations)"),
        ("Abdullah Farook", "Index: 22020112 / IS", "Role: UI/UX & Frontend Lead (Interface Layouts, Shared Design System)")
    ]

    for name, idx, role in team:
        p_t = doc.add_paragraph()
        style_para(p_t, 1, 2)
        r_name = p_t.add_run(f"• {name} ")
        r_name.font.name = "Arial"
        r_name.font.size = Pt(9.5)
        r_name.font.bold = True
        r_name.font.color.rgb = COLOR_PRIMARY

        r_det = p_t.add_run(f"({idx}) — {role}")
        r_det.font.name = "Arial"
        r_det.font.size = Pt(9)
        r_det.font.color.rgb = COLOR_DARK

    # Save Word Document
    doc_path = "/home/hasithx/project/USMS/IS_03.docx"
    doc.save(doc_path)
    print(f"Successfully generated: {doc_path}")

if __name__ == "__main__":
    create_document()
