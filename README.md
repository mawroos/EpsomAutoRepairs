# EpsomAutoRepairs

## Contact Form Setup

Contact forms work entirely client-side using **Web3Forms** for email delivery and **hCaptcha** for spam protection. No server-side code or PHP required — compatible with any static hosting (GitHub Pages, Netlify, Vercel, etc.).

### Quick Start

1. **Get a Web3Forms access key** (free, 250 submissions/month):
   - Go to [web3forms.com](https://web3forms.com/) and enter your email
   - You'll receive an access key — copy it
   - Open `js/contact-form-handler.js` and replace `YOUR_ACCESS_KEY_HERE` with your key

2. **Get an hCaptcha site key** (free):
   - Sign up at [hcaptcha.com](https://www.hcaptcha.com/)
   - Create a new site and copy the site key
   - In `index.html` and the `ajax-load/*.html` form files, replace `YOUR_HCAPTCHA_SITE_KEY` with your key

That's it — forms will now send emails to your inbox with spam protection.

### How It Works

- Forms submit via AJAX to the Web3Forms API (`https://api.web3forms.com/submit`)
- Web3Forms delivers the form data to your email inbox
- No backend, no PHP, no server configuration needed

### Spam Protection

Three layers of spam protection, all client-side:

1. **Honeypot field** — a hidden form field that bots fill in, triggering rejection
2. **hCaptcha** — "I'm not a robot" challenge (free, privacy-friendly alternative to reCAPTCHA)
3. **Web3Forms built-in filtering** — server-side spam detection by Web3Forms