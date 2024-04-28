# wordpress-cookie-consent-loader
Wordpress plugin to load scripts and styles for CookieConsent by Orest Bida

## Introduction
This plugin is designed to load the base Javascript and CSS styles for [CookieConsent by Orest Bida](https://cookieconsent.orestbida.com/) into Wordpress, so that you can load the `CookieConsent.run()` function using a tag manager. It also supports loading custom CSS in addition to the base files.

## Installation
1. Download all of the files from the **plugin** folder in this repository and add them to a folder called `cookie-consent-loader` within your Wordpress installations' `wp-content/plugins` directory.
2. Navigate to your Wordpress Admin console and enable the plugin.

## Configuration
When you download all of the files from this repository, the assets files are all (intentionally) empty. You need to update the CookieConsent files with the latest version yourself, in the Settings page:
1. Click the links to navigate to the official respository for the Javascript and CSS files
2. Copy and paste the code into the respective files using the file editor
3. If needed, add in custom CSS to `cookieconsent-custom.css` - [available CSS variables](https://cookieconsent.orestbida.com/advanced/ui-customization.html#available-css-variables)

> [!WARNING]
> The code editor text box has absolutely no syntax checking or error correction!
> Please check that the Javascript and CSS work as intended before saving.

## Tag Managers
Once the Wordpress site is loading the CookieConsent scripts (you should be able to open the browser console and confirm that there is a function available called `CookieConsent()`), you can configure your tag manager to load the consent banner and enforce end user consent decisions.

### Google Tag Manager ###
Note that this is a _very_ quick guide, and assumes you want to apply the same consent settings for all of your end users. If you also have IP Geolocation configured, you can customise this further to show different consent banners for end users in different countries.

1. Download the GTM Custom Template from the **tms/gtm** folder in this repository, and add the template to your GTM workspace.
2. In the Variables section, ensure that the Built-In Variables section lists Event | Custom Event. Enable this if it's not there.
3. In the Triggers section, add a "Consent Initialisation" trigger - type is Consent Initialisation. If you don't see this option, you need to enable it first.
4. Also in the Triggers section, add a "Consent Events" trigger - type is Custom Event, Event name is `onFirstConsent|onChange` - check the box for Use regex matching.
5. In the Tags section, you need at least three tags as follows:
   1. "Consent Mode - Default", with the following settings (assuming GDPR):
      * Tag Type: Consent Code for CookieConsent (from the template added above)
      * Consent Command: Default
      * Wait for Update: 500 (adjust to taste)
      * Include EEA Regions doesn't do anything at the moment..
      * Regions: all
      * All "Required for Google services": denied
      * All "Other signals": denied or Not set, depending on whether you're using these signals
      * Consent Cookie Name: cc_cookie
      * Consent Type Mappings: will change based on what you configure for your consent categories in the `CookieConsent.run()` command below
      * Firing Trigger: Consent Initialisation
   2. "Consent Mode - Update", with the following settings:
      * Tag Type: Consent Code for CookieConsent (from the template added above)
      * Consent Command: Update
      * All "Required for Google services": Not set
      * All "Other signals": Not set
      * Consent Cookie Name: cc_cookie
      * Consent Type Mappings: will change based on what you configure for your consent categories in the `CookieConsent.run()` command below
      * Firing Trigger: Consent Events
   3. "Consent Mode - Banner", with the following settings:
      * Tag Type: Custom HTML
      * HTML: see below
      * Firing Trigger: Consent Initialisation
  6. You should review the consent requirements for each of the tags you have configured. By default, Google tags will adjust behaviour depending on the consent state, but you may wish to add additional consent requirements for these - and for other tags you have configured.
  7. Test carefully using the Preview option; make sure the consent state is triggering / not triggering tags appropriately.

#### Custom HTML for GTM ####
Here is a very, _very_ minimal configuration for the banner - again assuming GDPR - this will need updating:

```html
<script>
CookieConsent.run({
  autoShow: true,
  mode: 'opt-in',
  guiOptions: {
    consentModal: {
      layout: 'cloud',
      position: 'bottom center',
      equalWeightButtons: false,
      flipButtons: false
    },
    preferencesModal: {
      layout: 'box',
      equalWeightButtons: false,
      flipButtons: false
    }
  },
  categories: {
    necessary: {
      readOnly: true,
      enabled: true
    },
    analytics: {},
    marketing: {}
  },
  onFirstConsent: function(detail) {
    dataLayer.push({
      event: 'onFirstConsent',
      consentCategories: detail.cookie.categories
    });
  },
  onChange: function(detail) {
    dataLayer.push({
      event: 'onChange',
      consentCategories: detail.cookie.categories
    });
  },
  language: {
    default: "en",
    autoDetect: "browser",
    translations: {
      en: {
        consentModal: {
          title: "Consent Banner Name",
          description: "This organisation uses browser storage (such as cookies) to improve how the website works.",
          acceptAllBtn: "Accept all",
          acceptNecessaryBtn: "Accept necessary",
          showPreferencesBtn: "Manage preferences"
        },
        preferencesModal: {
          title: "Consent Preferences Name",
          acceptAllBtn: "Accept all",
          acceptNecessaryBtn: "Accept necessary",
          savePreferencesBtn: "Save preferences",
          closeIconLabel: "Close modal",
          serviceCounterLabel: "Service|Services",
          sections: [
            {
              title: "Cookie Usage",
              description: "This organisation uses browser storage (such as cookies) to improve how the website works."
            },
            {
              title: "Strictly Necessary Cookies <span class=\"pm__badge\">Always Enabled</span>",
              description: "These cookies are required by the website and cannot be disabled.",
              linkedCategory: "necessary",
              cookieTable: {
                headers: {
                  name: "Name",
                  domain: "Service",
                  description: "Description",
                  expiration: "Expiration"
                },
                body: [
                  {
                    name: "comment_author*",
                    domain: "Wordpress",
                    description: "Used to remember your details when you leave a comment.",
                    expiration: "Expires after 1 year"
                  },
                  {
                    name: "cc_cookie",
                    domain: "Cookie Consent",
                    description: "Used to remember your cookie consent preferences.",
                    expiration: "Expires after 6 months"
                  }
                ]
              }
            },
            {
              title: "Analytics Cookies",
              description: "These cookies give us a better understanding of how you use the website.",
              linkedCategory: "analytics",
              cookieTable: {
                headers: {
                  name: "Name",
                  domain: "Service",
                  description: "Description",
                  expiration: "Expiration"
                },
                body: [
                  {
                    name: "_ga",
                    domain: "Google Analytics",
                    description: "Used to distinguish users.",
                    expiration: "Expires after 2 years"
                  },
                  {
                    name: "_ga_*",
                    domain: "Google Analytics",
                    description: "Used to persist session state.",
                    expiration: "Expires after 2 years"
                  }
                ]
              }
            },
            {
              title: "Marketing Cookies",
              description: "These cookies enable more relevant advertising and campaign performance monitoring.",
              linkedCategory: "marketing"
            },
            {
              title: "More information",
              description: "If you have any questions relating to our policy on cookies and your choices, please <a class=\"cc__link\" href=\"#\">contact us</a>."
            }
          ]
        }
      }
    }
  }
});
</script>
```
