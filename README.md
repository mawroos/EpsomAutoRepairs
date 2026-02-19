# EpsomAutoRepairs

## Email & Contact Form Setup

Contact forms use **SendGrid** for email delivery and **Google reCAPTCHA v2** for spam protection.

### Required Environment Variables

Set the following environment variables on your server (see `.env.example`):

| Variable | Description |
|---|---|
| `SENDGRID_API_KEY` | Your SendGrid API key ([get one here](https://sendgrid.com/docs/ui/account-and-settings/api-keys/)) |
| `CONTACT_EMAIL` | The email address that receives form submissions |
| `CONTACT_NAME` | The display name for the recipient |
| `RECAPTCHA_SITE_KEY` | Google reCAPTCHA v2 site key ([register here](https://www.google.com/recaptcha/admin)) |
| `RECAPTCHA_SECRET_KEY` | Google reCAPTCHA v2 secret key |

### reCAPTCHA Setup

1. Go to [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin) and register your site
2. Choose **reCAPTCHA v2** → "I'm not a robot" Checkbox
3. Add your domain(s) to the allowed domains list
4. Copy the **Site Key** and **Secret Key**
5. Set the environment variables `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY`
6. Replace `RECAPTCHA_SITE_KEY_PLACEHOLDER` in `index.html` and `ajax-load/modal-contact-form.html` with your actual site key

### SendGrid Setup

1. Create a free [SendGrid account](https://signup.sendgrid.com/)
2. Verify a [Sender Identity](https://docs.sendgrid.com/ui/sending-email/sender-verification) (the `CONTACT_EMAIL` address)
3. Create an [API key](https://docs.sendgrid.com/ui/account-and-settings/api-keys/) with "Mail Send" permission
4. Set the `SENDGRID_API_KEY` environment variable

### Spam Protection

The contact forms include three layers of spam protection:

1. **Honeypot field** — a hidden form field that bots typically fill in, triggering rejection
2. **Google reCAPTCHA v2** — "I'm not a robot" checkbox verification
3. **Rate limiting** — limits submissions to 5 per IP address within a 5-minute window