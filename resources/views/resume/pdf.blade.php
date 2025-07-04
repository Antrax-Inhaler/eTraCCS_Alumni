<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joven Andrei R. Lagahit - Full Stack Developer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary: #3a86ff;
            --secondary: #8338ec;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --success: #28a745;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.5;
            font-size: 14px;
        }
        
        .container {
            width: 8.5in;
            height: 13in;
            margin: 0;
            display: grid;
            grid-template-columns: 2.5in 6in;
            background: white;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 0.3in 0.2in;
            height: 13in;
            overflow: hidden;
        }
        
        .profile {
            text-align: center;
            margin-bottom: 0.3in;
        }
        
        .profile-img {
            width: 1.2in;
            height: 1.2in;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 0.2in;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
            margin-left: auto;
            margin-right: auto;
        }
        
        .profile h1 {
            font-size: 0.9rem;
            margin-bottom: 0.1in;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .profile p {
            font-size: 0.7rem;
            opacity: 0.9;
        }
        
        .contact-info, .skills, .soft-skills, .certifications {
            margin-bottom: 0.3in;
        }
        
        .contact-info h2, .skills h2, .soft-skills h2, .certifications h2 {
            font-size: 0.8rem;
            margin-bottom: 0.2in;
            position: relative;
            display: inline-block;
        }
        
        .contact-info h2::after, .skills h2::after, .soft-skills h2::after, .certifications h2::after {
            content: '';
            position: absolute;
            bottom: -0.05in;
            left: 0;
            width: 0.5in;
            height: 2px;
            background: white;
            border-radius: 3px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.15in;
            font-size: 0.6rem;
        }
        
        .contact-item i {
            margin-right: 0.1in;
            width: 0.2in;
            text-align: center;
            font-size: 0.6rem;
        }
        
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.1in;
        }
        
        .skill-tag {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.1in 0.15in;
            border-radius: 15px;
            font-size: 0.55rem;
        }
        
        .main-content {
            padding: 0.3in;
            height: 13in;
            overflow: hidden;
        }
        
        section {
            margin-bottom: 0.3in;
        }
        
        .section-title {
            font-size: 0.9rem;
            margin-bottom: 0.2in;
            color: var(--primary);
            position: relative;
            display: inline-block;
            font-weight: 700;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -0.05in;
            left: 0;
            width: 0.5in;
            height: 2px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 3px;
        }
        
        .objective {
            background: linear-gradient(to right, #f8f9fa, white);
            padding: 0.2in;
            border-radius: 5px;
            border-left: 3px solid var(--primary);
            font-size: 0.65rem;
            line-height: 1.4;
        }
        
        .project {
            margin-bottom: 0.2in;
            padding-bottom: 0.2in;
            border-bottom: 1px dashed #eee;
        }
        
        .project:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .project-title {
            font-size: 0.7rem;
            margin-bottom: 0.1in;
            color: var(--dark);
            font-weight: 600;
        }
        
        .project-description {
            color: var(--gray);
            font-size: 0.6rem;
            line-height: 1.4;
        }
        
        .education-item {
            display: flex;
            margin-bottom: 0.2in;
        }
        
        .education-year {
            min-width: 0.7in;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.6rem;
        }
        
        .education-details h3 {
            font-size: 0.7rem;
            margin-bottom: 0.05in;
        }
        
        .education-details p {
            color: var(--gray);
            font-size: 0.6rem;
        }
        
        .certification-item {
            margin-bottom: 0.15in;
        }
        
        .certification-item h3 {
            font-size: 0.6rem;
            margin-bottom: 0.05in;
        }
        
        .github .contact-item {
            color: var(--primary);
            margin-top: 0.1in;
            font-size: 0.6rem;
        }
        
        .github .contact-item i {
            font-size: 0.6rem;
        }
        
        /* Download button */
        .download-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(58, 134, 255, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
        }
        
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
                width: 100%;
                height: 100%;
            }
            
            .download-btn {
                display: none;
            }
        }
        .hobbies {
    margin-bottom: 0.3in;
}

.hobbies-list {
    font-size: 0.7rem;
    line-height: 1.6;
    color: var(--gray);
}

.hobbies-list p {
    margin-bottom: 0.15in;
    position: relative;
    padding-left: 0.2in;
}

.hobbies-list p:last-child {
    margin-bottom: 0;
}

.hobbies-list p::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.3em;
    width: 0.1in;
    height: 0.1in;
    background-color: var(--primary);
    border-radius: 50%;
}

.hobbies-list strong {
    color: var(--dark);
    font-weight: 600;
}

/* For the PDF version specifically */
@media print {
    .hobbies-list p::before {
        background-color: var(--primary) !important;
    }
}
    </style>
</head>
<body>
    <button class="download-btn" id="downloadPdf">
        <i class="fas fa-download"></i> Download PDF
    </button>

    <div class="container" id="resumeContent">
        <aside class="sidebar">
            <div class="profile">
                <div class="profile-img">J</div>
                <h1>Joven Andrei R. Lagahit</h1>
                <p>Full Stack Developer</p>
            </div>
            
            <div class="contact-info">
                <h2>Contact</h2>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Philippines</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-globe"></i>
                    <span>github.com/Antrax-Inhaler</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>your.email@example.com</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>09XXXXXXXXX</span>
                </div>
            </div>
            
            <div class="skills">
                <h2>Technical Skills</h2>
                <div class="skills-list">
                    <span class="skill-tag">HTML</span>
                    <span class="skill-tag">CSS</span>
                    <span class="skill-tag">JavaScript</span>
                    <span class="skill-tag">PHP</span>
                    <span class="skill-tag">Laravel</span>
                    <span class="skill-tag">Vue.js</span>
                    <span class="skill-tag">MySQL</span>
                    <span class="skill-tag">Git</span>
                    <span class="skill-tag">REST APIs</span>
                    <span class="skill-tag">MVC</span>
                    <span class="skill-tag">CRUD</span>
                    <span class="skill-tag">Authentication</span>
                </div>
            </div>
            
            <div class="soft-skills">
                <h2>Soft Skills</h2>
                <div class="skills-list">
                    <span class="skill-tag">Problem-Solving</span>
                    <span class="skill-tag">Teamwork</span>
                    <span class="skill-tag">Communication</span>
                    <span class="skill-tag">Time Management</span>
                    <span class="skill-tag">Adaptability</span>
                </div>
            </div>
            
            <div class="certifications">
                <h2>Certifications</h2>
                <div class="certification-item">
                    <h3>DICT Diagnostic Exam – Stage 1 Passed (2025)</h3>
                </div>
                <div class="certification-item">
                    <h3>MINSU C Programming 2 – Certification Exam</h3>
                </div>
            </div>
        </aside>
        
        <main class="main-content">
            <section class="objective">
                <h2 class="section-title">Career Objective</h2>
                <p>I am a highly resourceful and motivated individual with a positive outlook and strong problem-solving abilities. I approach challenges with patience, creativity, and a mindset focused on growth and possibilities. As a recent graduate with hands-on experience in full-stack web development, I am eager to contribute my technical skills, adaptability, and passion for building meaningful digital solutions in a team-oriented environment where I can grow and make a lasting impact.</p>
            </section>
            
            <section class="projects">
                <h2 class="section-title">Projects</h2>
                <div class="project">
                    <h3 class="project-title">Integrated HRIS with Multi-Payroll Processing, Biometric Attendance, and Recruitment Portal</h3>
                    <p class="project-description">Designed and developed a Laravel-based Human Resource Information System with modules for payroll processing, biometric attendance integration, and applicant tracking. Focused on backend architecture and data handling.</p>
                </div>
                <div class="project">
                    <h3 class="project-title">Interactive Cemetery Management System with Plot Reservation and Appointment Scheduling</h3>
                    <p class="project-description">Built a Laravel web application to manage cemetery plot data, enable online reservation, and schedule appointments. Implemented dynamic maps, admin controls, and status-based filtering.</p>
                </div>
                <div class="project">
                    <h3 class="project-title">Multi-Tenant Crop Farm Management System with Integrated Analytics and Mapping Features</h3>
                    <p class="project-description">Developed a full-stack farm management platform supporting multiple users and farms. Integrated real-time analytics dashboards and interactive farm mapping features using Leaflet.js and Laravel.</p>
                </div>
                <div class="project">
                    <h3 class="project-title">eTraCCS: A Mindoro State University College of Computer Studies Alumni Tracer Web Application</h3>
                    <p class="project-description">Capstone project focused on tracking alumni employment data, managing graduate profiles, and generating reports. Developed using Laravel and MySQL with a clean and responsive UI.</p>
                </div>
            </section>
            
            <section class="education">
                <h2 class="section-title">Education</h2>
                <div class="education-item">
                    <div class="education-year">2025</div>
                    <div class="education-details">
                        <h3>Bachelor of Science in Information Technology</h3>
                        <p>Mindoro State University – College of Computer Studies</p>
                    </div>
                </div>
            </section>
              <section class="hobbies">
                <h2 class="section-title">Hobbies & Interests</h2>
                <div class="hobbies-list">
                    <p><strong>Current Affairs:</strong> Passionate about staying informed with global news and technological advancements</p>
                    <p><strong>Music:</strong> Enjoy playing guitar and exploring various music genres</p>
                    <p><strong>Film:</strong> Avid movie enthusiast with particular interest in sci-fi and documentary films</p>
                </div>
            </section>
            <section class="github">
                <h2 class="section-title">GitHub</h2>
                <p>Explore my projects and contributions on GitHub:</p>
                <div class="contact-item">
                    <i class="fab fa-github"></i>
                    <span>github.com/Antrax-Inhaler</span>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.getElementById('downloadPdf').addEventListener('click', function() {
            const element = document.getElementById('resumeContent');
            const opt = {
                margin: 0,
                filename: 'Joven_Andrei_Lagahit_Resume.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2,
                    scrollX: 0,
                    scrollY: 0,
                    windowWidth: document.getElementById('resumeContent').scrollWidth,
                    windowHeight: document.getElementById('resumeContent').scrollHeight
                },
                jsPDF: { 
                    unit: 'in', 
                    format: [8.5, 13], 
                    orientation: 'portrait',
                    hotfixes: ['px_scaling']
                }
            };
            
            // Show loading state
            const btn = this;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
            btn.disabled = true;
            
            // Generate PDF
            html2pdf().set(opt).from(element).save().then(() => {
                btn.innerHTML = '<i class="fas fa-download"></i> Download PDF';
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>