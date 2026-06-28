# AI Assistant with Knowledge Graph

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://php.net)

> A full-featured enterprise WordPress AI chatbot plugin with knowledge graph visualization, multi-provider AI support, website content synchronization, analytics, compliance guardrails, and full branding customization.

---

## Features

### 🤖 Multi-Provider AI Support
Works with **OpenAI**, **Anthropic**, **Groq**, **Together AI**, **Ollama** (local), **LM Studio**, or any OpenAI-compatible API. Dynamic model fetching — no hardcoded model lists.

### 🧠 Knowledge Graph
Interactive Obsidian-style graph visualization showing relationships between your website content, user intents, and unanswered queries. See how your knowledge is connected.

### 📄 Website Content Sync
Automatically indexes WordPress pages and posts into a searchable knowledge base. The AI answers visitors using your actual content.

### 🎨 Theme Sync
Auto-detects your WordPress theme colors, fonts, and styling — the chatbot looks native on your site without extra configuration.

### 🌐 Multi-Language
Full English and Arabic support with auto-language detection, RTL layout, and contextual greetings.

### 📊 Analytics
Track chat volume, resolution rate, token usage, top queries, user intents, peak hours, and estimated cost. Export data to CSV.

### 🛡️ Compliance
AML guardrails, PII detection, configurable log retention, cookie consent (GDPR/CCPA/UAE PDPL), and complete audit trail.

### 🏷️ White-Label Branding
Customize assistant name, brand name, logo, welcome messages, header title, footer text, and colors — all from the admin panel.

---

## Screenshots

| Dashboard Overview | Knowledge Graph |
|:---:|:---:|
| ![Dashboard](01.png) | ![Knowledge Graph](02.png) |

---

## Installation

1. **Upload** the plugin folder to `/wp-content/plugins/` and activate
2. **Configure AI Provider** — Go to **AI Assistant > AI Provider**, select your provider (OpenAI, Anthropic, Groq, etc.) and enter your API key
3. **Fetch Models** — Click "Fetch Models" to load available models from your provider
4. **Sync Content** — Go to **AI Assistant > Dashboard** and click "Sync Website Content" to index your pages and posts
5. **Customize** — Configure widget appearance, greetings, branding, and business hours under **AI Assistant > Widget Settings**
6. **Go Live** — The chatbot appears automatically, or embed inline via shortcode/block/Elementor

## Embedding Options

- **Auto-widget** — Appears as a floating chat bubble on all pages (configurable)
- **Shortcode** — `[ceac_chatbot]` for inline embedding
- **Gutenberg Block** — Search "AI Assistant Chatbot" in the block editor
- **Elementor Widget** — Search "AI Assistant Chatbot" in Elementor widgets

## Environment Variables

| Variable | Description |
|----------|-------------|
| `CEAC_API_KEY` | Override the API key from settings (useful for CI/CD) |

## Requirements

- WordPress 6.0+
- PHP 7.4+
- A valid API key from any OpenAI-compatible provider

## Changelog

### 1.0.0
- Initial release — AI chatbot with knowledge graph, theme sync, multi-provider support, analytics, compliance, and full branding customization

---

## Support

- ⭐ **Star this repo** if you find it useful
- 🐛 [Open an issue](https://github.com/1Ayazahmed/AI-Chat-Assistant-for-WordPress-with-Multi-Provider-Support-Website-Content-Sync-Knowledge-Graph/issues) for bugs or feature requests
- 👨‍💻 Created by [Ayaz Ahmed](https://github.com/1Ayazahmed)

## License

GPL v2 or later
