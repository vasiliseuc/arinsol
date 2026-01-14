<?php
$cssVersion = file_exists('style.css') ? filemtime('style.css') : '1.0';
$jsonData = file_exists('data.json') ? file_get_contents('data.json') : '{}';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arinsol.ai</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="style.css?v=<?=$cssVersion?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <style>
        /* Custom Section Styles */
        .custom-section { background: #fff; }
        .custom-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .custom-card {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #eee;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .custom-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .custom-card p {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div id="loader-overlay">
        <div class="loader-spinner"></div>
        <div class="loader-text">Loading Arinsol </div>
    </div>

    <div id="main-content">
        
        <header class="site-header">
            <div class="container header-container">
                <div class="logo">
                    <a href="/" class="logo-link" title="Home">
                        <img id="header-logo" src="assets/logo.png" alt="Arinsol.ai" class="logo-img" loading="lazy">
                    </a>
                </div>
                <nav class="main-nav">
                    <ul id="nav-list">
                        </ul>
                </nav>
                <div class="header-social-icons" id="header-social-icons">
                </div>
                <a href="#contact" class="btn btn-gradient" id="header-cta"></a>
                <a href="#contact" class="mobile-contact-icon"><i class="fas fa-phone"></i></a>
                <div class="mobile-menu-icon"><i class="fas fa-bars"></i></div>
            </div>
        </header>

        <section id="hero" class="hero-section">
            <video class="hero-video" autoplay muted loop playsinline>
                <source src="assets/banner_video.mp4" type="video/mp4">
                <!-- fallback: silent autoplay may be blocked on some browsers -->
            </video>
            <div class="hero-overlay" aria-hidden="true"></div>
            <div class="container hero-content">
                <span class="sub-badge" id="hero-badge"></span>
                <h1 id="hero-title"></h1>
                <p id="hero-desc"></p>
                <div class="hero-buttons">
                    <a href="" class="btn btn-gradient" id="hero-cta"></a>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="section about-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="about-title"></h2>
                </div>
                <div class="about-content">
                    <div id="about-desc"></div>
                    <div class="principles-container">
                        <h3 id="principles-title"></h3>
                        <ul id="principles-list" class="principles-list"></ul>
                    </div>
                </div>
            </div>
        </section>

        <section id="services" class="section services-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="services-title"></h2>
                </div>
                <div class="services-grid" id="services-grid">
                    </div>
            </div>
        </section>

        <section id="industries" class="section industries-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="industries-title"></h2>
                    <p class="sub-text" id="industries-subtext"></p>
                </div>
                <div class="industries-grid" id="industries-grid"></div>
                <p class="text-center" id="industries-footer" style="margin-top: 30px; color: #666;"></p>
            </div>
        </section>

        <!-- Trust Section -->
        <section id="trust" class="section trust-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="trust-title"></h2>
                </div>
                <div class="trust-grid" id="trust-grid"></div>
            </div>
        </section>

        <section id="caseStudies" class="section case-studies-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="cs-title"></h2>
                    <p class="sub-text" id="cs-subtext"></p>
                </div>
                <div id="case-studies-container">
                    </div>
            </div>
        </section>

        <!-- Engagement Section -->
        <section id="engagement" class="section engagement-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="eng-title"></h2>
                </div>
                <div class="engagement-grid" id="eng-grid"></div>
                <div class="engagement-footer text-center">
                    <p id="eng-subtext"></p>
                    <a href="" class="btn btn-gradient" id="eng-cta"></a>
                </div>
            </div>
        </section>

        <section id="faq" class="section faq-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="faq-title"></h2>
                    <p class="sub-text" id="faq-subtext"></p>
                </div>
                <div class="faq-container" id="faq-items">
                    <!-- Items injected here -->
                </div>
            </div>
        </section>

        <section id="contact" class="section contact-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="contact-title"></h2>
                    <p class="sub-text" id="contact-subtext"></p>
                </div>

                <div class="contact-layout">
                    <div class="contact-main">
                        <!-- Product Selector -->
                        <div class="product-selector-wrapper">
                            <p class="selector-label">I’m interested in:</p>
                            <div class="product-options" id="product-options"></div>
                        </div>

                        <div class="contact-form-wrapper">
                            <form action="#" class="contact-form">
                                <p class="form-note"><span class="required-asterisk">*</span> Required fields</p>
                                <input type="hidden" id="input-product" name="product">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="input-name" class="form-label">Name <span class="required-asterisk">*</span></label>
                                        <input id="input-name" type="text" class="form-control" aria-label="Name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="input-email" class="form-label">Work Email <span class="required-asterisk">*</span></label>
                                        <input id="input-email" type="email" class="form-control" aria-label="Work Email" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="input-company" class="form-label">Company</label>
                                        <input id="input-company" type="text" class="form-control" aria-label="Company">
                                    </div>
                                    <div class="form-group">
                                        <label for="input-role" class="form-label">Role</label>
                                        <input id="input-role" type="text" class="form-control" aria-label="Role">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="input-country" class="form-label">Country / Time zone</label>
                                    <input id="input-country" type="text" class="form-control" aria-label="Country / Time zone">
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="input-industry" class="form-label">Industry (Optional)</label>
                                        <select id="input-industry" class="form-control" aria-label="Industry">
                                            <option value="" disabled selected>Select Industry</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="input-companysize" class="form-label">Company Size (Optional)</label>
                                        <select id="input-companysize" class="form-control" aria-label="Company Size">
                                            <option value="" disabled selected>Company Size</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="input-bottleneck" class="form-label">What is your biggest bottleneck? (Optional)</label>
                                    <input id="input-bottleneck" type="text" class="form-control" aria-label="Biggest bottleneck">
                                </div>

                                <div class="form-group">
                                    <label for="input-nextstep" class="form-label">Preferred Next Step (Optional)</label>
                                    <select id="input-nextstep" class="form-control" aria-label="Preferred next step">
                                        <option value="" disabled selected>Preferred Next Step</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="input-message" class="form-label">Message</label>
                                    <textarea id="input-message" class="form-control" rows="4" aria-label="Message"></textarea>
                                </div>

                                <div class="trust-lines" id="trust-lines"></div>

                                <div class="form-group captcha-group">
                                    <label id="captcha-label" class="form-label">Security Question: <span id="captcha-question"></span> <span class="required-asterisk">*</span></label>
                                    <input type="number" id="captcha-answer" class="form-control" placeholder="Your answer" required>
                                    <input type="hidden" id="captcha-value">
                                </div>
                                <div id="form-message" class="form-message" style="display: none;"></div>
                                <button type="submit" class="btn btn-gradient submit-btn" id="btn-submit">
                                    <span id="submit-text">Submit Request</span>
                                    <span id="submit-spinner" style="display: none;">Sending...</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="contact-sidebar">
                        <div class="what-next-box">
                            <h3>What happens next</h3>
                            <ol id="what-next-steps"></ol>
                        </div>
                        
                        <div class="contact-info-small">
                            <div class="stat-row" id="stats-grid">
                                <!-- Stats will be injected here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location Map -->
        <section id="location" class="section location-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="location-title">Our Location</h2>
                </div>
                <div id="map-container" style="width: 100%; height: 400px; border-radius: 12px; overflow: hidden; margin-top: 30px;">
                    <iframe 
                        id="location-map" 
                        width="100%" 
                        height="400" 
                        style="border:0" 
                        loading="lazy" 
                        allowfullscreen
                        src="">
                    </iframe>
                </div>
            </div>
        </section>

        <footer class="site-footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-social" id="footer-social">
                    </div>
                    <div class="footer-links">
                        <a href="#" id="terms-link">Terms & Conditions</a>
                        <span class="separator">|</span>
                        <a href="#" id="privacy-link">Privacy Policy</a>
                    </div>
                    <p id="footer-copy"></p>
                </div>
            </div>
        </footer>

        <!-- Cookie Consent Banner -->
        <div id="cookie-consent" class="cookie-consent" style="display: none;">
            <div class="cookie-content">
                <p>We use cookies to enhance your browsing experience and analyze our traffic. By clicking "Accept", you consent to our use of cookies. <a href="#privacy">Privacy Policy</a></p>
                <div class="cookie-buttons">
                    <button id="cookie-accept" class="btn btn-primary">Accept</button>
                    <button id="cookie-decline" class="btn btn-secondary">Decline</button>
                </div>
            </div>
        </div>

        <!-- Chatbot Widget -->
        <div id="chatbot-widget" class="chatbot-widget">
            <div class="chatbot-header">
                <h4>Chat with <span id="chatbot-name">Arin</span></h4>
                <button class="chatbot-close" id="chatbot-close">×</button>
            </div>
            <div class="chatbot-messages" id="chatbot-messages">
                <div class="chatbot-message bot-message">
                    <p>Hello! How can we help you today?</p>
                </div>
            </div>
            <div class="chatbot-input-area">
                <input type="text" id="chatbot-input" class="chatbot-input" placeholder="Type your message...">
                <button id="chatbot-send" class="chatbot-send">Send</button>
            </div>
        </div>

        <button id="chatbot-toggle" class="chatbot-toggle">
            <i class="fas fa-robot"></i>
        </button>
    </div>

    <script>
        const siteData = <?=$jsonData?>;
        document.addEventListener("DOMContentLoaded", () => {
            const loader = document.getElementById('loader-overlay');
            const content = document.getElementById('main-content');
            
            try {
                populateSite(siteData);
                
                // Simulate a small delay for the loader effect (optional)
                setTimeout(() => {
                    loader.classList.add('hidden');
                    content.classList.add('visible');
                }, 500);
            } catch (err) {
                console.error("Error processing data:", err);
                document.querySelector('.loader-text').innerText = "Error loading content. Please check console.";
            }
        });

        function populateSite(data) {
            // Safety: Ensure critical objects exist
            data.siteMeta = data.siteMeta || {};
            data.header = data.header || {};
            data.footer = data.footer || {};
            
            // --- CUSTOM CODE INJECTION ---
            if (data.customCode) {
                // Helper to insert and execute scripts
                const injectHTML = (html, target, prepend = false) => {
                    if (!html) return;
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    
                    const nodes = Array.from(temp.childNodes);
                    if (prepend) nodes.reverse();
                    
                    nodes.forEach(node => {
                        let newNode;
                        if (node.tagName === 'SCRIPT') {
                            newNode = document.createElement('script');
                            Array.from(node.attributes).forEach(attr => newNode.setAttribute(attr.name, attr.value));
                            newNode.text = node.textContent;
                        } else {
                            newNode = node.cloneNode(true);
                        }
                        
                        if (prepend) {
                            target.insertBefore(newNode, target.firstChild);
                        } else {
                            target.appendChild(newNode);
                        }
                    });
                };

                // Head Code
                if (data.customCode.head) {
                     injectHTML(data.customCode.head, document.head);
                }
                
                // CSS
                if (data.customCode.css) {
                    const style = document.createElement('style');
                    style.textContent = data.customCode.css;
                    document.head.appendChild(style);
                }
                
                // Body Start
                if (data.customCode.bodyStart) {
                    injectHTML(data.customCode.bodyStart, document.body, true);
                }
                
                // Body End
                if (data.customCode.bodyEnd) {
                    injectHTML(data.customCode.bodyEnd, document.body);
                }
            }
            
            // META
            if (data.siteMeta.title) document.title = data.siteMeta.title;
            
            // GOOGLE ANALYTICS
            if (data.siteMeta.googleAnalytics) {
                const gaId = data.siteMeta.googleAnalytics;
                // Add Google Tag Manager script
                const script1 = document.createElement('script');
                script1.async = true;
                script1.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
                document.head.appendChild(script1);
                
                // Add configuration script
                const script2 = document.createElement('script');
                script2.innerHTML = `
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', '${gaId}');
                `;
                document.head.appendChild(script2);
            }

            // HEADER
            try {
                const headerLogoEl = document.getElementById('header-logo');
                if (headerLogoEl) {
                    if (headerLogoEl.tagName && headerLogoEl.tagName.toLowerCase() === 'img') {
                        headerLogoEl.src = 'assets/logo.png';
                        headerLogoEl.alt = data.header.logoText || 'Arinsol.ai';
                    } else {
                        headerLogoEl.textContent = data.header.logoText;
                    }
                }
                const cta = document.getElementById('header-cta');
                if (cta) cta.textContent = data.header.ctaText;
                
                const navList = document.getElementById('nav-list');
                if (navList && data.header.navLinks) {
                    navList.innerHTML = ''; // Clear existing
                    data.header.navLinks.forEach(link => {
                        const li = document.createElement('li');
                        li.innerHTML = `<a href="${link.href}">${link.text}</a>`;
                        navList.appendChild(li);
                    });
                }
            } catch (e) { console.warn("Error rendering header:", e); }

            // CONTENT SECTIONS REORDERING
            // Define default order if not provided in JSON
            const defaultOrder = ['hero', 'about', 'services', 'industries', 'trust', 'caseStudies', 'engagement', 'faq', 'contact', 'location'];
            // If data has sectionOrder, use it. But we must include custom sections that might be in data but not in default order list.
            let sectionOrder = data.sectionOrder || defaultOrder;
            
            // Filter out disabled sections
            // We iterate sectionOrder. If a key is not in data, we skip. If disabled, we skip.
            // But we also need to render Custom Sections that are in data but might not be in DOM yet.
            // For custom sections, we create DOM elements on the fly.
            
            const mainContent = document.getElementById('main-content');
            const header = document.querySelector('.site-header');
            
            // Map keys to existing IDs for standard sections
            const keyToId = {
                'hero': 'hero',
                'about': 'about',
                'services': 'services',
                'industries': 'industries',
                'trust': 'trust',
                'caseStudies': 'caseStudies',
                'engagement': 'engagement',
                'faq': 'faq',
                'contact': 'contact',
                'location': 'location'
            };

            // Existing sections in DOM
            const sectionsMap = {};
            for (const [key, id] of Object.entries(keyToId)) {
                const el = document.getElementById(id);
                if (el) sectionsMap[key] = el;
            }

            // Hide all standard sections first
            Object.values(sectionsMap).forEach(el => el.style.display = 'none');

            // Re-insert in order after header
            let lastNode = header;
            sectionOrder.forEach(key => {
                const sectionData = data[key];
                
                // Skip if disabled
                if (sectionData && sectionData.disabled === true) return;
                
                // Standard Section
                if (sectionsMap[key]) {
                    const el = sectionsMap[key];
                    if (sectionData) {
                        el.style.display = 'block';
                        lastNode.parentNode.insertBefore(el, lastNode.nextSibling);
                        lastNode = el;
                        renderSection(key, data);
                    }
                } 
                // Custom Section (Create dynamically)
                else if (sectionData) {
                    let el = document.getElementById(key); // Check if already created (e.g. static html?)
                    if (!el) {
                        el = document.createElement('section');
                        el.id = key;
                        el.className = 'section custom-section';
                        // Insert
                        lastNode.parentNode.insertBefore(el, lastNode.nextSibling);
                    }
                    lastNode = el;
                    renderSection(key, data);
                }
            });

            // Handle Footer rendering
            try {
                 document.getElementById('footer-copy').innerHTML = data.footer.copyright || '';
                 
                 const termsLink = data.footer.termsLink || 'terms-conditions.php';
                 const privacyLink = data.footer.privacyLink || 'privacy-policy.php';
                 
                 const tEl = document.getElementById('terms-link');
                 if (tEl) tEl.href = termsLink;
                 
                 const pEl = document.getElementById('privacy-link');
                 if (pEl) pEl.href = privacyLink;

            } catch (e) { console.warn("Error rendering footer:", e); }
            
            // SOCIAL MEDIA
            try {
                if (data.socialMedia) {
                    const headerSocialIcons = document.getElementById('header-social-icons');
                    const footerSocial = document.getElementById('footer-social');
                    const socialLinks = [];
                    
                    if (data.socialMedia.facebook) {
                        socialLinks.push({url: data.socialMedia.facebook, icon: 'fab fa-facebook-f', name: 'Facebook'});
                    }
                    if (data.socialMedia.twitter) {
                        socialLinks.push({url: data.socialMedia.twitter, icon: 'fab fa-twitter', name: 'Twitter'});
                    }
                    if (data.socialMedia.linkedin) {
                        socialLinks.push({url: data.socialMedia.linkedin, icon: 'fab fa-linkedin-in', name: 'LinkedIn'});
                    }
                    if (data.socialMedia.instagram) {
                        socialLinks.push({url: data.socialMedia.instagram, icon: 'fab fa-instagram', name: 'Instagram'});
                    }
                    
                    // Clear existing
                    if (headerSocialIcons) headerSocialIcons.innerHTML = '';
                    if (footerSocial) footerSocial.innerHTML = '';

                    socialLinks.forEach(link => {
                        const a = document.createElement('a');
                        a.href = link.url;
                        a.target = '_blank';
                        a.rel = 'noopener noreferrer';
                        a.className = 'social-link';
                        a.innerHTML = `<i class="${link.icon}"></i>`;
                        a.title = link.name;
                        
                        // Add to header (main navigation area)
                        if (headerSocialIcons) {
                            headerSocialIcons.appendChild(a.cloneNode(true));
                        }
                        // Add to footer
                        if (footerSocial) {
                            footerSocial.appendChild(a.cloneNode(true));
                        }
                    });
                }
            } catch (e) { console.warn("Error rendering social media:", e); }
            
            // CHATBOT NAME
            if (data.chatbot && data.chatbot.name) {
                const chatbotNameElements = document.querySelectorAll('#chatbot-name, #chatbot-name-msg');
                chatbotNameElements.forEach(el => {
                    if (el) el.textContent = data.chatbot.name;
                });
            }
            
            // Re-initialize Mobile Menu Logic
            initMobileMenu();
        }

        function renderSection(key, data) {
            try {
                const sectionData = data[key];
                if (!sectionData) return;

                switch (key) {
                    case 'hero':
                        document.getElementById('hero-badge').textContent = sectionData.badge;
                        document.getElementById('hero-title').textContent = sectionData.title;
                        document.getElementById('hero-desc').textContent = sectionData.description;
                        const heroBtn = document.getElementById('hero-cta');
                        heroBtn.textContent = sectionData.ctaText;
                        heroBtn.href = sectionData.ctaLink;
                        
                        // Video Handling
                        const heroVideo = document.querySelector('.hero-video source');
                        if (heroVideo) {
                            const videoName = sectionData.video || 'banner_video.mp4';
                            const videoSrc = 'assets/' + videoName;
                            // Check if src is different to avoid unnecessary reload
                            if (!heroVideo.src.endsWith(videoSrc)) {
                                heroVideo.src = videoSrc;
                                heroVideo.parentElement.load();
                            }
                        }
                        break;
                    
                    case 'about':
                        const aboutTitle = document.getElementById('about-title');
                        if (aboutTitle) aboutTitle.textContent = sectionData.title;
                        
                        const descContainer = document.getElementById('about-desc');
                        if (descContainer) {
                            descContainer.innerHTML = '';
                            if (sectionData.description) {
                                const descLines = sectionData.description.split(/\n\s*\n|\n/);
                                descLines.forEach(line => {
                                    if (line.trim()) {
                                        const p = document.createElement('p');
                                        p.textContent = line.trim();
                                        descContainer.appendChild(p);
                                    }
                                });
                            }
                        }
                        
                        const principlesTitle = document.getElementById('principles-title');
                        if (principlesTitle) principlesTitle.textContent = sectionData.principlesTitle;
                        
                        const principlesList = document.getElementById('principles-list');
                        if (principlesList && sectionData.principles) {
                            principlesList.innerHTML = '';
                            sectionData.principles.forEach(p => {
                                const li = document.createElement('li');
                                li.textContent = p;
                                principlesList.appendChild(li);
                            });
                        }
                        break;

                    case 'services':
                        document.getElementById('services-title').textContent = sectionData.title;
                        const servicesGrid = document.getElementById('services-grid');
                        if (servicesGrid && sectionData.items) {
                            servicesGrid.innerHTML = '';
                            sectionData.items.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'service-card';
                                div.innerHTML = `
                                    <div class="icon ${item.colorClass || ''}"><i class="${item.iconClass || ''}"></i></div>
                                    <h3>${item.title || ''}</h3>
                                    <p>${item.description || ''}</p>
                                `;
                                servicesGrid.appendChild(div);
                            });
                        }
                        break;

                    case 'industries':
                        document.getElementById('industries-title').textContent = sectionData.title;
                        document.getElementById('industries-subtext').textContent = sectionData.subText;
                        if (sectionData.footerText) {
                            const footerEl = document.getElementById('industries-footer');
                            if (footerEl) footerEl.textContent = sectionData.footerText;
                        }
                        
                        const industriesGrid = document.getElementById('industries-grid');
                        if (industriesGrid && sectionData.items) {
                            industriesGrid.innerHTML = '';
                            sectionData.items.forEach(ind => {
                                const div = document.createElement('div');
                                div.className = 'industry-card';
                                div.innerHTML = `
                                    <div class="ind-header">
                                        <i class="${ind.iconClass || ''}" style="color: ${ind.color || ''};"></i>
                                        <h3>${ind.name || ''}</h3>
                                    </div>
                                    <p>${ind.description || ''}</p>
                                    <div class="ind-fit"><strong>Best fit:</strong> ${ind.bestFit || ''}</div>
                                `;
                                industriesGrid.appendChild(div);
                            });
                        }
                        break;
                    
                    case 'trust':
                        const trustTitle = document.getElementById('trust-title');
                        if (trustTitle) trustTitle.textContent = sectionData.title;
                        
                        const trustGrid = document.getElementById('trust-grid');
                        if (trustGrid && sectionData.items) {
                            trustGrid.innerHTML = '';
                            sectionData.items.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'trust-item';
                                div.innerHTML = `<i class="fas fa-check-circle" style="color: var(--blue-accent);"></i> <span>${item}</span>`;
                                trustGrid.appendChild(div);
                            });
                        }
                        break;

                    case 'caseStudies':
                        document.getElementById('cs-title').textContent = sectionData.title;
                        document.getElementById('cs-subtext').textContent = sectionData.subText;
                        const csContainer = document.getElementById('case-studies-container');
                        
                        if (csContainer && sectionData.items) {
                            csContainer.innerHTML = '';
                            sectionData.items.forEach((cs, index) => {
                                const row = document.createElement('div');
                                row.className = cs.reverseLayout ? 'case-study-row reverse' : 'case-study-row';
                                
                                let featuresHtml = '';
                                if (cs.features && cs.features.length > 0) {
                                    featuresHtml = '<ul class="cs-features">';
                                    cs.features.forEach(f => {
                                        featuresHtml += `<li>${f}</li>`;
                                    });
                                    featuresHtml += '</ul>';
                                }
                                
                                const contentHtml = `
                                    <div class="cs-content">
                                        <div class="cs-box">
                                            <h3>${cs.title || ''}</h3>
                                            <p>${cs.description || ''}</p>
                                            ${featuresHtml}
                                        </div>
                                    </div>`;
                                
                                const defaults = ['software1.jpg', 'software2.jpg'];
                                const imageFileName = cs.image || defaults[index % defaults.length];
                                const imageHtml = `
                                    <div class="cs-image">
                                        <img src="assets/${imageFileName}" alt="${cs.title || ''}" class="cs-image-img" loading="lazy">
                                    </div>`;
            
                                    row.innerHTML = contentHtml + imageHtml;
                            
                                csContainer.appendChild(row);
                            });
                        }
                        break;

                    case 'engagement':
                        const engTitle = document.getElementById('eng-title');
                        if (engTitle) engTitle.textContent = sectionData.title;
                        const engSubtext = document.getElementById('eng-subtext');
                        if (engSubtext) engSubtext.textContent = sectionData.subText;
                        
                        const engBtn = document.getElementById('eng-cta');
                        if (engBtn) {
                            engBtn.textContent = sectionData.ctaText;
                            engBtn.href = sectionData.ctaLink;
                        }
                        
                        const engGrid = document.getElementById('eng-grid');
                        if (engGrid && sectionData.items) {
                            engGrid.innerHTML = '';
                            sectionData.items.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'engagement-card';
                                div.innerHTML = `
                                    <p>${item.text || ''}</p>
                                    <a href="${item.link || '#'}" class="eng-link">${item.linkText || ''} →</a>
                                `;
                                engGrid.appendChild(div);
                            });
                        }
                        break;

                    case 'faq':
                        document.getElementById('faq-title').textContent = sectionData.title;
                        document.getElementById('faq-subtext').textContent = sectionData.subText;
                        
                        const faqContainer = document.getElementById('faq-items');
                        if (faqContainer && sectionData.items) {
                            faqContainer.innerHTML = '';
                            sectionData.items.forEach(item => {
                                const faqItem = document.createElement('div');
                                faqItem.className = 'faq-item';
                                
                                const question = document.createElement('div');
                                question.className = 'faq-question';
                                question.innerHTML = `<h3>${item.question || ''}</h3><span class="toggle-icon">+</span>`;
                                
                                const answer = document.createElement('div');
                                answer.className = 'faq-answer';
                                const contentHtml = parseFaqContent(item.answer);
                                answer.innerHTML = contentHtml;
                                
                                faqItem.appendChild(question);
                                faqItem.appendChild(answer);
                                faqContainer.appendChild(faqItem);
                                
                                question.addEventListener('click', () => {
                                    const isOpen = faqItem.classList.contains('active');
                                    document.querySelectorAll('.faq-item').forEach(fi => {
                                        fi.classList.remove('active');
                                        fi.querySelector('.toggle-icon').textContent = '+';
                                        fi.querySelector('.faq-answer').style.maxHeight = null;
                                    });
                                    if (!isOpen) {
                                        faqItem.classList.add('active');
                                        question.querySelector('.toggle-icon').textContent = '-';
                                        answer.style.maxHeight = answer.scrollHeight + "px";
                                    }
                                });
                            });
                        }
                        break;

                    case 'contact':
                        document.getElementById('contact-title').textContent = sectionData.title;
                        document.getElementById('contact-subtext').textContent = sectionData.subText;
            
                        // Stats (Sidebar)
                        const statsGrid = document.getElementById('stats-grid');
                        if (statsGrid && sectionData.stats) {
                            statsGrid.innerHTML = ''; // Clear existing
                            sectionData.stats.forEach(stat => {
                                const div = document.createElement('div');
                                div.className = 'stat-item-small';
                                div.innerHTML = `<strong>${stat.number || ''}</strong> <span>${stat.label || ''}</span>`;
                                statsGrid.appendChild(div);
                            });
                        }
            
                        // Products Selector
                        const productOptions = document.getElementById('product-options');
                        const inputProduct = document.getElementById('input-product');
                        if (productOptions && sectionData.products) {
                            productOptions.innerHTML = '';
                            sectionData.products.forEach(prod => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'product-btn';
                                btn.textContent = prod;
                                btn.onclick = function() {
                                    document.querySelectorAll('.product-btn').forEach(b => b.classList.remove('selected'));
                                    this.classList.add('selected');
                                    inputProduct.value = prod;
                                };
                                productOptions.appendChild(btn);
                            });
                        }
            
                        // Dropdowns
                        const industrySelect = document.getElementById('input-industry');
                        if (industrySelect && sectionData.industries) {
                            industrySelect.innerHTML = '<option value="" disabled selected>Select Industry</option>';
                            sectionData.industries.forEach(ind => {
                                const opt = document.createElement('option');
                                opt.value = ind;
                                opt.textContent = ind;
                                industrySelect.appendChild(opt);
                            });
                        }
            
                        const sizeSelect = document.getElementById('input-companysize');
                        if (sizeSelect && sectionData.companySizes) {
                            sizeSelect.innerHTML = '<option value="" disabled selected>Company Size</option>';
                            sectionData.companySizes.forEach(size => {
                                const opt = document.createElement('option');
                                opt.value = size;
                                opt.textContent = size;
                                sizeSelect.appendChild(opt);
                            });
                        }
            
                        const nextStepSelect = document.getElementById('input-nextstep');
                        if (nextStepSelect && sectionData.nextSteps) {
                            nextStepSelect.innerHTML = '<option value="" disabled selected>Preferred Next Step</option>';
                            sectionData.nextSteps.forEach(step => {
                                const opt = document.createElement('option');
                                opt.value = step;
                                opt.textContent = step;
                                nextStepSelect.appendChild(opt);
                            });
                        }
            
                        // Trust Lines
                        const trustLines = document.getElementById('trust-lines');
                        if (trustLines && sectionData.trustLines) {
                            trustLines.innerHTML = '';
                            sectionData.trustLines.forEach(line => {
                                const p = document.createElement('p');
                                p.innerHTML = `<i class="fas fa-check-circle"></i> ${line}`;
                                trustLines.appendChild(p);
                            });
                        }
            
                        // What Next
                        const whatNext = document.getElementById('what-next-steps');
                        if (whatNext && sectionData.whatNextSteps) {
                            whatNext.innerHTML = '';
                            sectionData.whatNextSteps.forEach(step => {
                                const li = document.createElement('li');
                                li.textContent = step;
                                whatNext.appendChild(li);
                            });
                        }
            
                        // Form Labels
                        const fl = sectionData.formLabels;
                        if (fl) {
                            const setPH = (id, txt) => {
                                const el = document.getElementById(id);
                                if (el) { el.placeholder = txt; el.setAttribute('aria-label', txt); }
                            };
                            setPH('input-name', fl.name);
                            setPH('input-email', fl.email);
                            setPH('input-company', fl.company);
                            setPH('input-role', fl.role);
                            setPH('input-country', fl.country);
                            setPH('input-bottleneck', fl.bottleneck);
                            setPH('input-message', fl.message);
                            
                            if (fl.industry && industrySelect && industrySelect.options.length > 0) industrySelect.options[0].textContent = fl.industry;
                            if (fl.companySize && sizeSelect && sizeSelect.options.length > 0) sizeSelect.options[0].textContent = fl.companySize;
                            if (fl.nextStep && nextStepSelect && nextStepSelect.options.length > 0) nextStepSelect.options[0].textContent = fl.nextStep;
                            
                            const sBtnText = document.getElementById('submit-text');
                            if (sBtnText) sBtnText.textContent = fl.submit;
                        }
                        break;

                    case 'location':
                        if (sectionData) {
                             const mapFrame = document.getElementById('location-map');
                             if (mapFrame) {
                                 if (sectionData.latitude && sectionData.longitude) {
                                     const mapUrl = `https://www.google.com/maps?q=${sectionData.latitude},${sectionData.longitude}&output=embed`;
                                     mapFrame.src = mapUrl;
                                 } else if (sectionData.address) {
                                     const address = encodeURIComponent(`${sectionData.address} ${sectionData.city || ''} ${sectionData.country || ''}`.trim());
                                     const mapUrl = `https://www.google.com/maps?q=${address}&output=embed`;
                                     mapFrame.src = mapUrl;
                                 }
                             }
                             // Update title if exists in data (it's not in original json but good for consistency)
                             if (sectionData.title) {
                                 const locTitle = document.getElementById('location-title');
                                 if (locTitle) locTitle.textContent = sectionData.title;
                             }
                        }
                        break;
                    
                    default:
                        // Custom Section Rendering
                        const el = document.getElementById(key);
                        if (el) {
                            // Check type
                            const type = sectionData.type || 'generic';
                            
                            if (type === 'richtext') {
                                el.innerHTML = `
                                    <div class="container">
                                        <div class="section-header text-center">
                                            <h2>${sectionData.title || ''}</h2>
                                        </div>
                                        <div class="custom-richtext" style="max-width:800px;margin:0 auto;line-height:1.6">
                                            ${sectionData.content || ''}
                                        </div>
                                    </div>
                                `;
                            } else if (type === 'video') {
                                // Simple video embed
                                el.innerHTML = `
                                    <div class="container">
                                        <div class="section-header text-center">
                                            <h2>${sectionData.title || ''}</h2>
                                        </div>
                                        <div class="custom-video" style="max-width:800px;margin:0 auto;text-align:center">
                                            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;background:#000">
                                                <iframe src="${sectionData.videoUrl || ''}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen></iframe>
                                            </div>
                                            <p style="margin-top:15px;color:#666">${sectionData.caption || ''}</p>
                                        </div>
                                    </div>
                                `;
                            } else if (type === 'testimonials') {
                                el.innerHTML = `
                                    <div class="container">
                                        <div class="section-header text-center">
                                            <h2>${sectionData.title || ''}</h2>
                                        </div>
                                        <div class="custom-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                                            ${(sectionData.items || []).map(item => {
                                                const imgHtml = item.image 
                                                    ? `<img src="${item.image}" alt="${item.name || ''}" style="width:80px;height:80px;border-radius:50%;margin-bottom:15px;object-fit:cover;" loading="lazy">`
                                                    : `<div style="width:80px;height:80px;border-radius:50%;margin-bottom:15px;background:#e5e7eb;display:inline-flex;align-items:center;justify-content:center;color:#9ca3af;font-size:2rem;"><i class="fas fa-user"></i></div>`;
                                                
                                                return `
                                                <div class="custom-card" style="text-align:center; padding:30px; background:#f9f9f9;">
                                                    ${imgHtml}
                                                    <p style="font-style:italic;font-size:1.1rem;color:#555;margin-bottom:20px;">"${item.quote || ''}"</p>
                                                    <h4 style="margin:0;color:#333;font-weight:700;">${item.name || ''}</h4>
                                                    <span style="color:#777;font-size:0.9rem;">${item.role || ''}</span>
                                                </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                `;
                            } else if (type === 'cta') {
                                const bgColor = sectionData.bgColor || '#f3f4f6';
                                const textColor = (parseInt(bgColor.replace('#', ''), 16) > 0xffffff / 2) ? '#111' : '#fff'; // simple contrast check
                                el.innerHTML = `
                                    <div class="container">
                                        <div style="background:${bgColor}; color:${textColor}; padding:60px 30px; border-radius:16px; text-align:center;">
                                            <h2 style="margin:0 0 15px; color:${textColor}; font-size:2rem;">${sectionData.title || ''}</h2>
                                            <p style="margin:0 0 30px; opacity:0.9; max-width:600px; margin-left:auto; margin-right:auto;">${sectionData.subText || ''}</p>
                                            <a href="${sectionData.btnLink || '#'}" class="btn" style="background:${textColor}; color:${bgColor}; padding:12px 30px; border-radius:30px; text-decoration:none; font-weight:600; display:inline-block;">${sectionData.btnText || 'Learn More'}</a>
                                        </div>
                                    </div>
                                `;
                            } else if (type === 'team') {
                                el.innerHTML = `
                                    <div class="container">
                                        <div class="section-header text-center">
                                            <h2>${sectionData.title || ''}</h2>
                                            <p class="sub-text">${sectionData.subText || ''}</p>
                                        </div>
                                        <div class="custom-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                                            ${(sectionData.items || []).map(item => {
                                                const imgHtml = item.photo 
                                                    ? `<img src="${item.photo}" alt="${item.name || ''}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">`
                                                    : `<div style="width:100%;height:100%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:4rem;"><i class="fas fa-user"></i></div>`;
                                                
                                                return `
                                                <div class="custom-card" style="text-align:center;">
                                                    <div style="width:100%;height:250px;background:#eee;border-radius:8px;margin-bottom:15px;overflow:hidden;">
                                                        ${imgHtml}
                                                    </div>
                                                    <h3 style="margin:10px 0 5px;font-size:1.2rem;">${item.name || ''}</h3>
                                                    <p style="color:var(--primary);font-weight:600;margin-bottom:10px;">${item.role || ''}</p>
                                                    <p style="font-size:0.9rem;color:#666;margin-bottom:15px;">${item.bio || ''}</p>
                                                    ${item.linkedin ? `<a href="${item.linkedin}" target="_blank" style="color:#0077b5;font-size:1.2rem;"><i class="fab fa-linkedin"></i></a>` : ''}
                                                </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                `;
                            } else {
                                // Generic (existing)
                                el.innerHTML = `
                                    <div class="container">
                                        <div class="section-header text-center">
                                            <h2>${sectionData.title || ''}</h2>
                                            <p class="sub-text">${sectionData.subText || ''}</p>
                                        </div>
                                        <div class="custom-grid">
                                            ${(sectionData.items || []).map(item => `
                                                <div class="custom-card">
                                                    <h3>${item.title || ''}</h3>
                                                    <p>${item.description || ''}</p>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                `;
                            }
                        }
                        break;
                }
            } catch (e) {
                console.error(`Error rendering section ${key}:`, e);
            }
        }

        function initMobileMenu() {
            const menuIcon = document.querySelector('.mobile-menu-icon');
            const navMenu = document.querySelector('.main-nav');

            // Clone to remove old event listeners if any (simple reset)
            const newMenuIcon = menuIcon.cloneNode(true);
            menuIcon.parentNode.replaceChild(newMenuIcon, menuIcon);
            
            const finalMenuIcon = document.querySelector('.mobile-menu-icon');

            finalMenuIcon.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                const icon = finalMenuIcon.querySelector('i');
                if (navMenu.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });

            document.querySelectorAll('.main-nav a').forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                    finalMenuIcon.querySelector('i').classList.remove('fa-times');
                    finalMenuIcon.querySelector('i').classList.add('fa-bars');
                });
            });
        }

        // CHATBOT FUNCTIONALITY
        const chatbotWidget = document.getElementById('chatbot-widget');
        const chatbotToggle = document.getElementById('chatbot-toggle');
        const chatbotClose = document.getElementById('chatbot-close');
        const chatbotInput = document.getElementById('chatbot-input');
        const chatbotSend = document.getElementById('chatbot-send');
        const chatbotMessages = document.getElementById('chatbot-messages');

        // Toggle chatbot visibility
        chatbotToggle.addEventListener('click', () => {
            chatbotWidget.classList.toggle('active');
        });

        chatbotClose.addEventListener('click', () => {
            chatbotWidget.classList.remove('active');
        });

        // Send message function
        function sendMessage() {
            const message = chatbotInput.value.trim();
            if (message === '') return;

            // Add user message
            const userMsgDiv = document.createElement('div');
            userMsgDiv.className = 'chatbot-message user-message';
            userMsgDiv.innerHTML = `<p>${escapeHtml(message)}</p>`;
            chatbotMessages.appendChild(userMsgDiv);

            chatbotInput.value = '';
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;

            // Simulate bot response
            setTimeout(() => {
                const botMsgDiv = document.createElement('div');
                botMsgDiv.className = 'chatbot-message bot-message';
                botMsgDiv.innerHTML = `<p>Thanks for your message! Our team will get back to you shortly. Please feel free to fill out the contact form for a quicker response.</p>`;
                chatbotMessages.appendChild(botMsgDiv);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }, 500);
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        chatbotSend.addEventListener('click', sendMessage);
        chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function parseFaqContent(text) {
            if (!text) return '';
            const lines = text.split('\n');
            let html = '';
            let inList = false;
            
            lines.forEach(line => {
                line = line.trim();
                if (!line) {
                    if (inList) { html += '</ul>'; inList = false; }
                    html += '<br>';
                    return;
                }
                
                if (line.startsWith('•')) {
                    if (!inList) { html += '<ul class="faq-list">'; inList = true; }
                    html += `<li>${line.substring(1).trim()}</li>`;
                } else {
                    if (inList) { html += '</ul>'; inList = false; }
                    
                    // Simple bold detection for headings
                    if (line.includes(':') && line.length < 60 && !line.includes('.')) {
                         html += `<h4>${line}</h4>`;
                    } else {
                         html += `<p>${line}</p>`;
                    }
                }
            });
            
            if (inList) html += '</ul>';
            return html;
        }

        // COOKIE CONSENT
        function showCookieConsent() {
            const consent = localStorage.getItem('cookieConsent');
            if (!consent) {
                const cookieBanner = document.getElementById('cookie-consent');
                if (cookieBanner) {
                    cookieBanner.style.display = 'block';
                }
            }
        }

        function hideCookieConsent() {
            const cookieBanner = document.getElementById('cookie-consent');
            if (cookieBanner) {
                cookieBanner.style.display = 'none';
            }
        }

        const cookieAccept = document.getElementById('cookie-accept');
        const cookieDecline = document.getElementById('cookie-decline');
        
        if (cookieAccept) {
            cookieAccept.addEventListener('click', () => {
                localStorage.setItem('cookieConsent', 'accepted');
                hideCookieConsent();
            });
        }

        if (cookieDecline) {
            cookieDecline.addEventListener('click', () => {
                localStorage.setItem('cookieConsent', 'declined');
                hideCookieConsent();
            });
        }

        // Show cookie consent on page load
        showCookieConsent();

        // CONTACT FORM SUBMISSION
        const contactForm = document.querySelector('.contact-form');
        const formMessage = document.getElementById('form-message');
        const submitBtn = document.getElementById('btn-submit');
        const submitText = document.getElementById('submit-text');
        const submitSpinner = document.getElementById('submit-spinner');
        
        // Generate random math captcha
        function generateCaptcha() {
            const num1 = Math.floor(Math.random() * 10) + 1;
            const num2 = Math.floor(Math.random() * 10) + 1;
            const answer = num1 + num2;
            
            document.getElementById('captcha-question').textContent = `${num1} + ${num2} = ?`;
            document.getElementById('captcha-value').value = answer;
            document.getElementById('captcha-answer').value = '';
        }
        
        // Generate captcha on page load
        generateCaptcha();
        
        if (contactForm) {
            contactForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Get form values
                const name = document.getElementById('input-name').value.trim();
                const email = document.getElementById('input-email').value.trim();
                const product = document.getElementById('input-product').value;
                
                const company = document.getElementById('input-company').value.trim();
                const role = document.getElementById('input-role').value.trim();
                const country = document.getElementById('input-country').value.trim();
                
                const industry = document.getElementById('input-industry').value;
                const companySize = document.getElementById('input-companysize').value;
                const bottleneck = document.getElementById('input-bottleneck').value.trim();
                const nextStep = document.getElementById('input-nextstep').value;
                
                const message = document.getElementById('input-message').value.trim();
                const captchaAnswer = document.getElementById('captcha-answer').value.trim();
                const captchaValue = document.getElementById('captcha-value').value;
                
                // Basic validation
                if (!name || !email) {
                    showFormMessage('Name and Email are required.', 'error');
                    return;
                }
                
                if (!captchaAnswer || parseInt(captchaAnswer) !== parseInt(captchaValue)) {
                    showFormMessage('Incorrect answer to security question. Please try again.', 'error');
                    generateCaptcha(); // Generate new captcha
                    return;
                }
                
                // Disable submit button
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitSpinner.style.display = 'inline';
                formMessage.style.display = 'none';
                
                // Prepare form data
                const formData = new FormData();
                formData.append('name', name);
                formData.append('email', email);
                formData.append('product', product);
                formData.append('company', company);
                formData.append('role', role);
                formData.append('country', country);
                formData.append('industry', industry);
                formData.append('company_size', companySize);
                formData.append('bottleneck', bottleneck);
                formData.append('next_step', nextStep);
                formData.append('message', message);
                formData.append('captcha_answer', captchaAnswer);
                formData.append('captcha_value', captchaValue);
                
                try {
                    const response = await fetch('email/email.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showFormMessage(result.message || 'Thank you! Your message has been sent successfully.', 'success');
                        contactForm.reset();
                        generateCaptcha(); // Generate new captcha
                    } else {
                        showFormMessage(result.error || 'Failed to send message. Please try again.', 'error');
                        generateCaptcha(); // Generate new captcha on error
                    }
                } catch (error) {
                    showFormMessage('Network error. Please check your connection and try again.', 'error');
                    generateCaptcha();
                } finally {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitSpinner.style.display = 'none';
                }
            });
        }
        
        function showFormMessage(message, type) {
            formMessage.textContent = message;
            formMessage.className = 'form-message ' + type;
            formMessage.style.display = 'block';
            
            // Scroll to message
            formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    formMessage.style.display = 'none';
                }, 5000);
            }
        }
    </script>
</body>
</html>