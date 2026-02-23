# EpsomAutoRepairs

## Contact Form Setup

Contact forms work entirely client-side using **Web3Forms** for email delivery and **hCaptcha** for spam protection. No server-side code or PHP required — compatible with any static hosting (GitHub Pages, Netlify, Vercel, etc.).

### Quick Start

1. **Get a Web3Forms access key** (free, 250 submissions/month):
   - Go to [web3forms.com](https://web3forms.com/) and enter your email
   - You'll receive an access key — copy it

2. **Get an hCaptcha site key** (free):
   - Sign up at [hcaptcha.com](https://www.hcaptcha.com/)
   - Create a new site and copy the site key

3. **Add both keys as GitHub Secrets** (so they're injected automatically on deploy):
   - Go to your repo → **Settings** → **Secrets and variables** → **Actions**
   - Click **New repository secret** and add:
     - Name: `WEB3FORMS_ACCESS_KEY` — Value: your Web3Forms access key
     - Name: `HCAPTCHA_SITE_KEY` — Value: your hCaptcha site key

4. **Push to `main`** — the GitHub Actions workflow will automatically replace the placeholders with your keys and deploy to GitHub Pages.

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