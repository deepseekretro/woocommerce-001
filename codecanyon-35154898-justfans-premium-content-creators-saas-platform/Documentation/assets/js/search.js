document.addEventListener('DOMContentLoaded', function () {
	const searchInput = document.getElementById('search-input');
	const searchResultsContainer = document.getElementById('search-results');

	if (!searchInput || !searchResultsContainer) {
		console.error('Search input or results container not found!');
		return;
	}

	// Define the search data
	//TODO: Updated meta
	const data = [
		{
			"section": "Introduction",
			"category": "Getting started",
			"subCategory": "",
			"title": "Documentation introduction",
			"description": "An overview of the documentation.",
			"keywords": ["introduction", "overview", "getting started"],
			"link": "#introduction"
		},
		{
			"section": "Requirements",
			"category": "Getting started",
			"subCategory": "",
			"title": "System Requirements",
			"description": "Hardware and software requirements.",
			"keywords": ["requirements", "hardware", "software", "prerequisites"],
			"link": "#requirements"
		},
		{
			"section": "General",
			"category": "Installation",
			"subCategory": "",
			"title": "Installation guide",
			"description": "Guide for installing the script.",
			"keywords": ["installation", "setup", "guide"],
			"link": "#installation"
		},
		{
			"section": "Files Setup",
			"category": "Installation",
			"subCategory": "",
			"title": "Preparing the files",
			"description": "Preparing your script for installation.",
			"keywords": ["files setup", "script installation", "configuration"],
			"link": "#files-setup"
		},
		{
			"section": "DocumentRoot",
			"category": "Installation",
			"subCategory": "Files Setup",
			"title": "Removing /public path",
			"description": "Preparing your script document root.",
			"keywords": ["docroot", "documentroot"],
			"link": "#docroot"
		},
		{
			"section": "Database Setup",
			"category": "Installation",
			"subCategory": "",
			"title": "Configuring the database",
			"description": "Setting up and configuring the database.",
			"keywords": ["database", "configuration", "setup"],
			"link": "#database-setup"
		},
		{
			"section": "Installer",
			"category": "Installation",
			"subCategory": "",
			"title": "Running the installer",
			"description": "Instructions for running the installer.",
			"keywords": ["installer", "installation process", "setup"],
			"link": "#installer"
		},
		{
			"section": "Payments",
			"category": "Configuration",
			"subCategory": "",
			"title": "Payment gateways setup",
			"description": "Configuring payment gateways for transactions.",
			"keywords": ["payments", "gateway", "transactions", "setup"],
			"link": "#payments"
		},

		{
			"section": "Stripe",
			"category": "Configuration",
			"subCategory": "Payments",
			"title": "Setting up Stripe",
			"description": "Guide for setting up Stripe as a payment gateway.",
			"keywords": ["stripe", "payment gateway", "api keys", "webhooks", "oxxo", "iDEAL", "Blik", "Bancontact", "EPS", "Giropay", "Przelewy24"],
			"link": "#stripe"
		},
		{
			"section": "PayPal",
			"category": "Configuration",
			"subCategory": "Payments",
			"title": "Setting up PayPal",
			"description": "Guide for configuring PayPal for payments.",
			"keywords": ["paypal", "payment gateway", "api keys", "webhooks"],
			"link": "#paypal"
		},
		{
			"section": "Coinbase",
			"category": "Configuration",
			"subCategory": "Payments",
			"title": "Setting up Coinbase Commerce",
			"description": "Guide for configuring Coinbase Commerce for cryptocurrency payments.",
			"keywords": ["coinbase", "cryptocurrency", "payment gateway", "api keys", "webhooks"],
			"link": "#coinbase"
		},
		{
			"section": "NowPayments",
			"category": "Configuration",
			"subCategory": "Payments",
			"title": "Setting up NowPayments",
			"description": "Guide for configuring NowPayments for cryptocurrency transactions.",
			"keywords": ["nowpayments", "cryptocurrency", "payment gateway", "api keys"],
			"link": "#nowpayments"
		},
		{
			"section": "CCBill",
			"category": "Configuration",
			"subCategory": "Payments",
			"title": "Setting up CCBill",
			"description": "Guide for configuring CCBill as a payment processor.",
			"keywords": ["ccbill", "payment gateway", "recurring payments", "webhooks"],
			"link": "#ccbill"
		},
		{
			"section": "Paystack",
			"category": "Configuration",
			"subCategory": "Payments",
			"title": "Setting up Paystack",
			"description": "Guide for configuring Paystack as a payment gateway.",
			"keywords": ["paystack", "payment gateway", "api keys", "webhooks"],
			"link": "#paystack"
		},
		{
			"section": "MercadoPago",
			"category": "Configuration",
			"subCategory": "Payments",
			"title": "Setting up MercadoPago",
			"description": "Guide for configuring MercadoPago as a payment gateway.",
			"keywords": ["MercadoPago", "payment gateway", "api keys"],
			"link": "#mercadopago"
		},
		{
			"section": "Offline payments",
			"category": "Configuration",
			"subCategory": "Payments",
			"title": "Setting up Offline payments",
			"description": "Guide for configuring offline payments as a payment gateway.",
			"keywords": ["offline payments", "payment gateway", "api keys"],
			"link": "#offline-payments"
		},
		{
			"section": "Withdrawals",
			"category": "Configuration",
			"subCategory": "",
			"title": "Withdrawal methods",
			"description": "Configuring Withdrawal methods for users.",
			"keywords": ["withdrawals", "payouts"],
			"link": "#withdrawals"
		},

		{
			"section": "Manual",
			"category": "Configuration",
			"subCategory": "Withdrawals",
			"title": "Using Manual Withdrawals",
			"description": "The Manual Withdrawals can be used to payout the creators.",
			"keywords": ["Manual Withdrawals", "manual payout"],
			"link": "#manual-withdrawals"
		},

		{
			"section": "Stripe Connect",
			"category": "Configuration",
			"subCategory": "Withdrawals",
			"title": "Using Stripe Connect",
			"description": "Stripe connect Withdrawals can be used to payout the creators.",
			"keywords": ["Stripe Withdrawals", "stripe payout", "stripe connect"],
			"link": "#stripe-connect"
		},

		{
			"section": "Crons setup",
			"category": "Configuration",
			"subCategory": "",
			"title": "Setting up Cron Jobs",
			"description": "Setting up cron jobs for automated tasks.",
			"keywords": ["cron jobs", "automation", "task scheduling"],
			"link": "#crons"
		},
		{
			"section": "Emails",
			"category": "Configuration",
			"subCategory": "",
			"title": "Configuring Email Settings",
			"description": "Email configuration and template setup.",
			"keywords": ["emails", "configuration", "template setup"],
			"link": "#emails"
		},

		{
			"section": "Log Driver",
			"category": "Configuration",
			"subCategory": "Emails",
			"title": "Using the Log Driver",
			"description": "The Log driver records outgoing emails to logs for debugging purposes.",
			"keywords": ["log driver", "debugging", "email logs"],
			"link": "#emails-log-driver"
		},
		{
			"section": "Sendmail Driver",
			"category": "Configuration",
			"subCategory": "Emails",
			"title": "Using the Sendmail Driver",
			"description": "The Sendmail driver relies on PHP's mail() function, often enabled on shared hosting.",
			"keywords": ["sendmail driver", "php mail", "shared hosting"],
			"link": "#emails-sendmail-driver"
		},
		{
			"section": "Mailgun Driver",
			"category": "Configuration",
			"subCategory": "Emails",
			"title": "Using the Mailgun Driver",
			"description": "The Mailgun driver requires a domain, API key, and endpoint to send emails.",
			"keywords": ["mailgun", "api key", "email domain", "email driver"],
			"link": "#emails-mailgun-driver"
		},
		{
			"section": "SMTP Driver",
			"category": "Configuration",
			"subCategory": "Emails",
			"title": "Using the SMTP Driver",
			"description": "The SMTP driver requires a host, port, encryption, username, and password to send emails.",
			"keywords": ["smtp", "email host", "tls", "ssl", "email configuration"],
			"link": "#emails-smtp-driver"
		},

		{
			"section": "Websockets",
			"category": "Configuration",
			"subCategory": "",
			"title": "Configuring Websockets",
			"description": "Websockets configuration .",
			"keywords": ["Websockets"],
			"link": "#websockets"
		},

		{
			"section": "Pusher",
			"category": "Configuration",
			"subCategory": "Websockets",
			"title": "Using pusher websockets",
			"description": "Using Pusher driver for websockets.",
			"keywords": ["smtp", "email host", "tls", "ssl", "email configuration"],
			"link": "#websockets-pusher"
		},

		{
			"section": "Soketi",
			"category": "Configuration",
			"subCategory": "Websockets",
			"title": "Using soketi websockets",
			"description": "Using soketi driver for websockets.",
			"keywords": ["smtp", "email host", "tls", "ssl", "email configuration"],
			"link": "#websockets-soketi"
		},

		{
			"section": "Storage",
			"category": "Configuration",
			"subCategory": "",
			"title": "Configuring Storage",
			"description": "Setting up local or cloud storage.",
			"keywords": ["storage", "local", "cloud", "setup"],
			"link": "#storage"
		},
		{
			"section": "Amazon S3",
			"category": "Configuration",
			"subCategory": "Storage",
			"title": "Configuring Amazon S3",
			"description": "Guide for configuring Amazon S3 storage, including CloudFront and signed URLs.",
			"keywords": ["amazon s3", "cloud storage", "aws", "cloudfront", "signed urls", "presigned", "presigned urls"],
			"link": "#s3"
		},
		{
			"section": "Wasabi",
			"category": "Configuration",
			"subCategory": "Storage",
			"title": "Configuring Wasabi Storage",
			"description": "Guide for setting up Wasabi storage and bucket policies.",
			"keywords": ["wasabi", "cloud storage", "bucket policies", "cloud hosting"],
			"link": "#wasabi"
		},
		{
			"section": "DigitalOcean Spaces",
			"category": "Configuration",
			"subCategory": "Storage",
			"title": "Configuring DigitalOcean Spaces",
			"description": "Guide for setting up DigitalOcean Spaces storage and keys.",
			"keywords": ["digitalocean", "spaces", "cloud storage", "do keys", "buckets"],
			"link": "#do-spaces"
		},
		{
			"section": "Minio",
			"category": "Configuration",
			"subCategory": "Storage",
			"title": "Configuring Minio",
			"description": "Guide for setting up Minio as a self-hosted storage solution.",
			"keywords": ["minio", "self-hosted storage", "buckets", "access keys"],
			"link": "#minio"
		},
		{
			"section": "PushrCDN",
			"category": "Configuration",
			"subCategory": "Storage",
			"title": "Configuring PushrCDN",
			"description": "Guide for setting up PushrCDN with AWS S3-compatible storage.",
			"keywords": ["pushrcdn", "cdn", "cloud storage", "aws s3 compatible"],
			"link": "#pushrcdn"
		},

		{
			"section": "Video Transcoding",
			"category": "Configuration",
			"subCategory": "",
			"title": "Configuring Video Transcoding",
			"description": "Setting up local or video transcoding.",
			"keywords": ["videos transcoding", "video encoding", "video"],
			"link": "#video-transcoding"
		},

		{
			"section": "FFMpeg",
			"category": "Configuration",
			"subCategory": "Video Transcoding",
			"title": "Configuring FFMpeg",
			"description": "Guide for setting up FFMpeg for video transcoding.",
			"keywords": ["ffmpeg"],
			"link": "#ffmpeg-driver"
		},

		{
			"section": "Coconut",
			"category": "Configuration",
			"subCategory": "Video Transcoding",
			"title": "Configuring Coconut",
			"description": "Guide for setting up Coconut.co for video transcoding.",
			"keywords": ["Coconut.co", "coconut"],
			"link": "#coconut-driver"
		},

		{
			"section": "Live Streaming",
			"category": "Configuration",
			"subCategory": "",
			"title": "Setting up streaming",
			"description": "Setting up streaming.",
			"keywords": ["live streaming", "streaming"],
			"link": "#streaming"
		},

		{
			"section": "Captcha",
			"category": "Configuration",
			"subCategory": "",
			"title": "Setting up Captcha",
			"description": "Adding CAPTCHA for security purposes.",
			"keywords": ["captcha", "security", "anti-bot"],
			"link": "#captcha"
		},
		{
			"section": "reCAPTCHA",
			"category": "Configuration",
			"subCategory": "Captcha",
			"title": "Setting up Google reCAPTCHA",
			"description": "Guide for configuring Google reCAPTCHA to protect forms from spam and abuse.",
			"keywords": ["google recaptcha", "captcha", "anti-bot", "security"],
			"link": "#google-recaptcha"
		},
		{
			"section": "hCaptcha",
			"category": "Configuration",
			"subCategory": "Captcha",
			"title": "Setting up hCaptcha",
			"description": "Guide for configuring hCaptcha as a privacy-focused alternative to reCAPTCHA.",
			"keywords": ["hcaptcha", "captcha", "anti-bot", "privacy"],
			"link": "#hcaptcha"
		},
		{
			"section": "Turnstile",
			"category": "Configuration",
			"subCategory": "Captcha",
			"title": "Setting up Turnstile Captcha",
			"description": "Guide for configuring Turnstile Captcha for form protection and enhanced security.",
			"keywords": ["turnstile captcha", "captcha", "anti-bot", "security"],
			"link": "#turnstile-captcha"
		},

		{
			"section": "GEO-Blocking",
			"category": "Configuration",
			"subCategory": "",
			"title": "Setting up GEO-Blocking",
			"description": "Enabling GEO-Blocking for users.",
			"keywords": ["GEO-Blocking"],
			"link": "#geo-blocking"
		},

		{
			"section": "Social login",
			"category": "Configuration",
			"subCategory": "",
			"title": "Setting up Social Login",
			"description": "Enabling social login for users.",
			"keywords": ["social login", "authentication", "user login"],
			"link": "#social-login"
		},
		{
			"section": "Twitter",
			"category": "Configuration",
			"subCategory": "Social Login",
			"title": "Setting up Twitter Login",
			"description": "Guide for configuring Twitter login with API keys and callback URLs.",
			"keywords": ["twitter login", "api keys", "social login", "oauth"],
			"link": "#twitter-login"
		},
		{
			"section": "Google",
			"category": "Configuration",
			"subCategory": "Social Login",
			"title": "Setting up Google Login",
			"description": "Guide for configuring Google login with OAuth credentials and redirect URIs.",
			"keywords": ["google login", "oauth", "social login", "client id", "client secret"],
			"link": "#google-login"
		},
		{
			"section": "Facebook",
			"category": "Configuration",
			"subCategory": "Social Login",
			"title": "Setting up Facebook Login",
			"description": "Guide for configuring Facebook login with App ID, App Secret, and redirect URIs.",
			"keywords": ["facebook login", "app id", "app secret", "social login", "oauth"],
			"link": "#facebook-login"
		},
		{
			"section": "OpenAI",
			"category": "Configuration",
			"subCategory": "",
			"title": "Integrating OpenAI",
			"description": "Connecting OpenAI to your platform.",
			"keywords": ["openai", "integration", "api"],
			"link": "#openai"
		},
		{
			"section": "Referrals",
			"category": "Configuration",
			"subCategory": "",
			"title": "Referrals",
			"description": "Adding Referrals to your platform.",
			"keywords": ["Referrals", "marketing"],
			"link": "#referrals"
		},
		{
			"section": "Languages",
			"category": "Configuration",
			"subCategory": "",
			"title": "Managing Languages",
			"description": "Adding and managing languages.",
			"keywords": ["languages", "localization", "translation"],
			"link": "#translations"
		},
		{
			"section": "Admin panel",
			"category": "Management",
			"subCategory": "",
			"title": "Admin Panel Overview",
			"description": "Using the admin panel effectively.",
			"keywords": ["admin panel", "management", "tools"],
			"link": "#admin"
		},
		{
			"section": "Update",
			"category": "Maintenance",
			"subCategory": "",
			"title": "Updating the script",
			"description": "Instructions to update the your site.",
			"keywords": ["update", "system maintenance", "upgrades"],
			"link": "#update"
		},
		{
			"section": "FAQ",
			"category": "General",
			"subCategory": "Support",
			"title": "Frequently Asked Questions",
			"description": "Commonly asked questions.",
			"keywords": ["faq", "support", "questions"],
			"link": "#faq"
		}
	];

	// Set up Fuse.js options
	const options = {
		keys: ['title', 'description', 'category', 'subCategory', 'section', 'keywords'],
		threshold: 0.3,
	};

	// Initialize Fuse.js with the data
	const fuse = new Fuse(data, options);

	// Function to render search results
	function renderResults(results) {
		// Clear previous results
		searchResultsContainer.innerHTML = '';

		// If there are no results, show the "No results found" message
		if (results.length === 0) {
			const noResultsItem = document.createElement('li');
			noResultsItem.classList.add('list-group-item', 'p-3', 'text-center');
			noResultsItem.textContent = '🤷‍♂️ No results found';
			searchResultsContainer.appendChild(noResultsItem);
			searchResultsContainer.style.display = 'block'; // Ensure it's visible
			return;
		}

		// If results are found, display them in the dropdown
		results.forEach(result => {
			const { title, category, subCategory, section, link } = result.item;

			// Create a breadcrumb string
			let breadcrumb = '';
			if (subCategory.length > 0) {
				breadcrumb = `${category} > ${subCategory} > ${section}`;
			} else {
				breadcrumb = `${category} > ${section}`;
			}

			const listItem = document.createElement('li');
			listItem.classList.add('list-group-item', 'p-2', 'border-0', 'd-flex', 'flex-column');

			listItem.innerHTML = `
                <a href="documentation.html${link}" class="text-decoration-none">
                    <strong>${title}</strong><br>
                    <small class="text-muted">${breadcrumb}</small>
                </a>
            `;

			// Append the item to the results container
			searchResultsContainer.appendChild(listItem);
		});

		// Show the results dropdown
		searchResultsContainer.style.display = 'block';
	}

	// Search input event listener
	searchInput.addEventListener('input', function () {
		const query = this.value.trim();

		if (query.length > 0) {
			const results = fuse.search(query);
			renderResults(results);
		} else {
			// If the search input is empty, hide the dropdown
			searchResultsContainer.style.display = 'none';
		}
	});

	// Hide search results when clicking outside or pressing Escape
	document.addEventListener('click', function (event) {
		if (!searchResultsContainer.contains(event.target) && event.target !== searchInput) {
			searchResultsContainer.style.display = 'none';
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			searchResultsContainer.style.display = 'none'; // Hide results on Escape key
		}
	});
});
