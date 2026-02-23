# EpsomAutoRepairs

## Contact Form Setup

Contact forms work entirely client-side using **Web3Forms** for email delivery. No server-side code or PHP required — compatible with any static hosting (GitHub Pages, Netlify, Vercel, etc.).

### Quick Start

1. **Get a Web3Forms access key** (free, 250 submissions/month):
   - Go to [web3forms.com](https://web3forms.com/) and enter your email
   - You'll receive an access key — copy it

2. **Add your key as a GitHub Secret** (so it's injected automatically on deploy):
   - Go to your repo → **Settings** → **Secrets and variables** → **Actions**
   - Click **New repository secret** and add:
     - Name: `WEB3FORMS_ACCESS_KEY` — Value: your Web3Forms access key

4. **Push to `main`** — the GitHub Actions workflow will automatically replace the placeholders with your keys and deploy to GitHub Pages.

That's it — forms will now send emails to your inbox with spam protection.

### How It Works

- Forms submit via AJAX to the Web3Forms API (`https://api.web3forms.com/submit`)
- Web3Forms delivers the form data to your email inbox
- No backend, no PHP, no server configuration needed

### Spam Protection

Two layers of spam protection:

1. **Honeypot field** — a hidden form field that bots fill in, triggering rejection
2. **Web3Forms built-in filtering** — server-side spam detection by Web3Forms