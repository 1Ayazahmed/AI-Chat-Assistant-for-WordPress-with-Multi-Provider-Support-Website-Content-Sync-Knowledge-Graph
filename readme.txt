=== AI Assistant with Knowledge Graph ===
Contributors: ayazahmed
Tags: chatbot, ai, openai, customer support, knowledge graph, wordpress chatbot
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

AI chatbot with knowledge graph, theme sync, website content indexing, and OpenAI-compatible providers. Multi-language, analytics, and smart fallback.

== Description ==

AI Assistant with Knowledge Graph is a full-featured enterprise WordPress plugin providing intelligent chatbot capabilities with seamless WordPress integration.

![Dashboard Overview](https://via.placeholder.com/800x450?text=AI+Assistant+Dashboard)
*Screenshot: Dashboard overview with setup progress and analytics*

**Key Features:**

* **Intelligent Theme Synchronization** — Automatically matches your WordPress theme colors, fonts, and styling for a native look and feel
* **Multi-Provider AI Support** — Works with OpenAI, Anthropic, Groq, Together AI, Ollama (local), LM Studio, or any OpenAI-compatible API. Dynamic model fetching from your provider.
* **Website Content Indexing & Knowledge Brain** — Automatically indexes your WordPress pages and posts into a searchable knowledge base. The AI uses your actual website content to answer visitor questions accurately.
* **Knowledge Graph Visualization** — Interactive Obsidian-style graph showing relationships between knowledge entries, user intents, and unanswered queries
* **Smart Fallback & Human Escalation** — When confidence is low or queries are out of scope, gracefully falls back with helpful options or escalates to your team via email or webhook (CRM integration)
* **English & Arabic Multi-Language** — Full RTL support for Arabic, auto-language detection based on browser preference, and contextual greetings
* **Analytics Dashboard** — Track chat volume, resolution rates, token usage, top queries, user intents, peak hours, and estimated cost
* **Compliance & Audit Trail** — AML guardrails, PII detection, configurable log retention, cookie consent (GDPR/CCPA/UAE PDPL), and full audit logging
* **Configurable Branding** — Customize assistant name, brand name, logo, welcome messages, chat header title, footer text, and colors from the admin panel
* **Flexible Embedding** — Gutenberg block, shortcode `[ceac_chatbot]`, and Elementor widget for inline or widget placement
* **Business Hours** — Show offline form with email collection outside configured business hours
* **Rate Limiting & Token Budgets** — Daily token caps and per-chat budgets to control API costs

== Screenshots ==

1. Dashboard overview with setup wizard and statistics
2. Knowledge graph visualization showing content relationships
3. Chat widget frontend with customizable appearance
4. Analytics dashboard with intent tracking and cost monitoring

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` and activate
2. Go to **AI Assistant > AI Provider** and configure your API endpoint (OpenAI, Anthropic, Groq, etc.)
3. Click **"Fetch Models"** to load available models from your provider
4. Go to **AI Assistant > Dashboard** and click **"Sync Website Content"** to index your pages
5. Customize the widget appearance, greetings, and branding under **AI Assistant > Widget Settings**
6. Configure fallback, escalation, and compliance settings under **AI Assistant > Settings**

== Shortcode ==

`[ai_assistant]` — Embed the chatbot inline anywhere on your site
`[ceac_chatbot]` — Legacy shortcode (still supported for backward compatibility)

== Gutenberg Block ==

Search for "AI Assistant Chatbot" in the block editor and add it to any post or page.

== Elementor Widget ==

Search for "AI Assistant Chatbot" in Elementor widgets and drag it onto your page.

== Environment Variables ==

`CEAC_API_KEY` — Override the API key set in settings (useful for CI/CD or managed hosting)

== Changelog ==

= 1.0.0 =
* Initial release — AI chatbot with knowledge graph, theme sync, multi-provider support, analytics, compliance, and full branding customization
