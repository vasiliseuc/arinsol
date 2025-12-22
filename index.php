<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arinsol.ai</title>
    <script>
        const cacheToken = Math.random().toString(36).substring(2, 15);
        document.write(`<link rel="stylesheet" href="style.css?v=${cacheToken}">`);
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                        <img id="header-logo" src="assets/logo.png" alt="Arinsol.ai" class="logo-img">
                    </a>
                </div>
                <nav class="main-nav">
                    <ul id="nav-list">
                        </ul>
                </nav>
                <div class="header-social-icons" id="header-social-icons">
                </div>
                <a href="#contact" class="btn btn-gradient" id="header-cta"></a>
                <div class="mobile-menu-icon"><i class="fas fa-bars"></i></div>
            </div>
        </header>

        <section class="hero-section">
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
                <div class="industries-grid" id="industries-grid">
                    </div>
            </div>
        </section>

        <section id="case-studies" class="section case-studies-section">
            <div class="container">
                <div class="section-header text-center">
                    <h2 id="cs-title"></h2>
                    <p class="sub-text" id="cs-subtext"></p>
                </div>
                <div id="case-studies-container">
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
                    <div class="contact-info">
                        <div class="big-stat">
                            <span class="number"><span id="years-exp"></span><span class="plus">+</span></span>
                            <span class="label" id="years-label"></span>
                        </div>
                        <h3 class="success-title"><span id="success-title"></span> <span class="highlight" id="success-subtitle"></span></h3>
                        
                        <div class="stats-grid-small" id="stats-grid">
                            </div>
                    </div>

                    <div class="contact-form-wrapper">
                        <form action="#" class="contact-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <input id="input-name" type="text" class="form-control" aria-label="Name">
                                </div>
                                <div class="form-group">
                                    <input id="input-email" type="email" class="form-control" aria-label="Email">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <input id="input-phone" type="text" class="form-control" aria-label="Phone">
                                </div>
                                <div class="form-group">
                                    <input id="input-skype" type="text" class="form-control" aria-label="Skype">
                                </div>
                            </div>
                            <div class="form-group">
                                <textarea id="input-desc" class="form-control" rows="4" aria-label="Project Description"></textarea>
                            </div>
                            <div class="form-group file-upload">
                                <label id="lbl-upload"></label>
                                <div class="file-box">
                                    <button type="button" id="btn-choose"></button>
                                    <span id="txt-nofile"></span>
                                </div>
                            </div>
                            <div class="form-group captcha-group">
                                <label id="captcha-label">Security Question: <span id="captcha-question"></span></label>
                                <input type="number" id="captcha-answer" class="form-control" placeholder="Your answer" required>
                                <input type="hidden" id="captcha-value">
                            </div>
                            <div id="form-message" class="form-message" style="display: none;"></div>
                            <button type="submit" class="btn btn-gradient submit-btn" id="btn-submit">
                                <span id="submit-text"></span>
                                <span id="submit-spinner" style="display: none;">Sending...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Location Map -->
            <div class="container" style="margin-top: 60px;">
                <div class="section-header text-center">
                    <h2>Our Location</h2>
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
        document.addEventListener("DOMContentLoaded", () => {
            const loader = document.getElementById('loader-overlay');
            const content = document.getElementById('main-content');
            const cacheToken = Math.random().toString(36).substring(2, 15);

            // FETCH DATA
            fetch(`data.json?v=${cacheToken}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("HTTP error " + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    populateSite(data);
                    
                    // Simulate a small delay for the loader effect (optional)
                    setTimeout(() => {
                        loader.classList.add('hidden');
                        content.classList.add('visible');
                    }, 500); 
                })
                .catch(err => {
                    console.error("Error loading JSON data:", err);
                    document.querySelector('.loader-text').innerText = "Error loading content.";
                });
        });

        function populateSite(data) {
            // META
            document.title = data.siteMeta.title;

            // HEADER
            const headerLogoEl = document.getElementById('header-logo');
            if (headerLogoEl) {
                if (headerLogoEl.tagName && headerLogoEl.tagName.toLowerCase() === 'img') {
                    // Use provided asset as main logo; set alt from data if available
                    headerLogoEl.src = 'assets/logo.png';
                    headerLogoEl.alt = data.header.logoText || 'Arinsol.ai';
                } else {
                    headerLogoEl.textContent = data.header.logoText;
                }
            }
            document.getElementById('header-cta').textContent = data.header.ctaText;
            
            const navList = document.getElementById('nav-list');
            data.header.navLinks.forEach(link => {
                const li = document.createElement('li');
                li.innerHTML = `<a href="${link.href}">${link.text}</a>`;
                navList.appendChild(li);
            });

            // HERO
            document.getElementById('hero-badge').textContent = data.hero.badge;
            document.getElementById('hero-title').textContent = data.hero.title;
            document.getElementById('hero-desc').textContent = data.hero.description;
            const heroBtn = document.getElementById('hero-cta');
            heroBtn.textContent = data.hero.ctaText;
            heroBtn.href = data.hero.ctaLink;

            // SERVICES
            document.getElementById('services-title').textContent = data.services.title;
            const servicesGrid = document.getElementById('services-grid');
            data.services.items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'service-card';
                div.innerHTML = `
                    <div class="icon ${item.colorClass}"><i class="${item.iconClass}"></i></div>
                    <h3>${item.title}</h3>
                    <p>${item.description}</p>
                `;
                servicesGrid.appendChild(div);
            });

            // INDUSTRIES
            document.getElementById('industries-title').textContent = data.industries.title;
            document.getElementById('industries-subtext').textContent = data.industries.subText;
            const industriesGrid = document.getElementById('industries-grid');
            data.industries.items.forEach(ind => {
                const div = document.createElement('div');
                div.className = 'industry-card';
                div.innerHTML = `<i class="${ind.iconClass}" style="color: ${ind.color};"></i> <span>${ind.name}</span>`;
                industriesGrid.appendChild(div);
            });

            // CASE STUDIES
            document.getElementById('cs-title').textContent = data.caseStudies.title;
            document.getElementById('cs-subtext').textContent = data.caseStudies.subText;
            const csContainer = document.getElementById('case-studies-container');
            
            data.caseStudies.items.forEach((cs, index) => {
                const row = document.createElement('div');
                row.className = cs.reverseLayout ? 'case-study-row reverse' : 'case-study-row';
                
                const contentHtml = `
                    <div class="cs-content">
                        <div class="cs-box">
                            <h3>${cs.title}</h3>
                            <p>${cs.description}</p>
                            <a href="#" class="btn btn-gradient-small">${cs.ctaText}</a>
                        </div>
                    </div>`;
                
                // Use image from JSON data, fallback to software1.jpg or software2.jpg
                const imageFileName = cs.image || (index === 0 ? 'software1.jpg' : 'software2.jpg');
                const imageHtml = `
                    <div class="cs-image">
                        <img src="assets/${imageFileName}" alt="${cs.title}" class="cs-image-img">
                    </div>`;

                if (cs.reverseLayout) {
                    row.innerHTML = imageHtml + contentHtml;
                } else {
                    row.innerHTML = contentHtml + imageHtml;
                }
                csContainer.appendChild(row);
            });

            // CONTACT
            document.getElementById('contact-title').textContent = data.contact.title;
            document.getElementById('contact-subtext').textContent = data.contact.subText;
            document.getElementById('years-exp').textContent = data.contact.yearsExp;
            document.getElementById('years-label').textContent = data.contact.yearsLabel;
            document.getElementById('success-title').textContent = data.contact.successTitle;
            document.getElementById('success-subtitle').textContent = data.contact.successSubtitle;

            const statsGrid = document.getElementById('stats-grid');
            data.contact.stats.forEach(stat => {
                const div = document.createElement('div');
                div.className = 'stat-box';
                div.innerHTML = `<h4>${stat.number}</h4><p>${stat.label}</p>`;
                statsGrid.appendChild(div);
            });

            // Form placeholders (labels removed visually; set accessible placeholders)
            const fl = data.contact.formLabels;
            const inpName = document.getElementById('input-name');
            const inpEmail = document.getElementById('input-email');
            const inpPhone = document.getElementById('input-phone');
            const inpSkype = document.getElementById('input-skype');
            const inpDesc = document.getElementById('input-desc');
            if (inpName) { inpName.placeholder = fl.name; inpName.setAttribute('aria-label', fl.name); }
            if (inpEmail) { inpEmail.placeholder = fl.email; inpEmail.setAttribute('aria-label', fl.email); }
            if (inpPhone) { inpPhone.placeholder = fl.phone; inpPhone.setAttribute('aria-label', fl.phone); }
            if (inpSkype) { inpSkype.placeholder = fl.skype; inpSkype.setAttribute('aria-label', fl.skype); }
            if (inpDesc) { inpDesc.placeholder = fl.projectDesc; inpDesc.setAttribute('aria-label', fl.projectDesc); }
            // File upload labels/buttons
            const lblUpload = document.getElementById('lbl-upload');
            if (lblUpload) { lblUpload.textContent = fl.upload; }
            const btnChoose = document.getElementById('btn-choose');
            if (btnChoose) { btnChoose.textContent = fl.chooseFile; }
            const txtNofile = document.getElementById('txt-nofile');
            if (txtNofile) { txtNofile.textContent = fl.noFile; }
            const submitBtnText = document.getElementById('submit-text');
            if (submitBtnText) {
                submitBtnText.textContent = fl.submit;
            } else {
                document.getElementById('btn-submit').textContent = fl.submit;
            }

            // FOOTER
            document.getElementById('footer-copy').innerHTML = data.footer.copyright;
            if (data.footer.termsLink) {
                document.getElementById('terms-link').href = data.footer.termsLink;
            }
            if (data.footer.privacyLink) {
                document.getElementById('privacy-link').href = data.footer.privacyLink;
            }
            
            // SOCIAL MEDIA
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
            
            // CHATBOT NAME
            if (data.chatbot && data.chatbot.name) {
                const chatbotNameElements = document.querySelectorAll('#chatbot-name, #chatbot-name-msg');
                chatbotNameElements.forEach(el => {
                    if (el) el.textContent = data.chatbot.name;
                });
            }
            
            // LOCATION MAP
            if (data.location) {
                const mapFrame = document.getElementById('location-map');
                if (mapFrame && data.location.latitude && data.location.longitude) {
                    // Using Google Maps embed without API key (works for basic embeds)
                    const address = encodeURIComponent(`${data.location.address || ''} ${data.location.city || ''} ${data.location.country || ''}`.trim());
                    const mapUrl = `https://www.google.com/maps?q=${data.location.latitude},${data.location.longitude}&output=embed`;
                    mapFrame.src = mapUrl;
                } else if (mapFrame && data.location.address) {
                    // Fallback to address-based search
                    const address = encodeURIComponent(`${data.location.address} ${data.location.city} ${data.location.country}`.trim());
                    const mapUrl = `https://www.google.com/maps?q=${address}&output=embed`;
                    mapFrame.src = mapUrl;
                }
            }
            
            // Re-initialize Mobile Menu Logic (since nav items were dynamic)
            initMobileMenu();
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
                const phone = document.getElementById('input-phone').value.trim();
                const skype = document.getElementById('input-skype').value.trim();
                const message = document.getElementById('input-desc').value.trim();
                const captchaAnswer = document.getElementById('captcha-answer').value.trim();
                const captchaValue = document.getElementById('captcha-value').value;
                
                // Basic validation
                if (!name || !email || !message) {
                    showFormMessage('Please fill in all required fields.', 'error');
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
                formData.append('phone', phone);
                formData.append('skype', skype);
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