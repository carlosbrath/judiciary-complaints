
Build an Overseas Judiciary Complaint Portal for the Lower Judiciary of AJK, using D:\Herd\complaints as the reference for architecture, UI and code style. Use the same stack: Laravel 12, Livewire 3, Tailwind CSS v4 (Vite), Pest, with the same fonts (Bricolage Grotesque for headings, Public Sans for body text, Noto Nastaliq Urdu for Urdu) and an English/Urdu locale switcher.

Public side: copy the modern landing page from resources/views/public/home.blade.php. Overseas citizens register and log in, then submit a complaint about a court case or any problem they faced during proceedings. The form asks court/district, case number, case title, complaint category, description and optional attachments. Model it on resources/js/complaint-form.js.

Citizen dashboard: each complaint gets a unique tracking number and is registered against the citizen's account. Citizens see a status timeline (Submitted → Under Review → In Progress → Resolved/Closed) and every review or remark the admin writes, in a clean, read-only thread.

Chatbot: add a floating assistant like resources/js/chatbot.js. It answers FAQs, explains how to file a complaint, and checks complaint status by tracking number.

Admin panel: one role only (Admin), no departments and no permission system. It has a dashboard with stat cards (total, pending, in progress, resolved), a complaint list with search and filters (status, court, country, date), and a complaint detail page. From the detail page the admin changes status and adds follow-up reviews, which the citizen sees.

Out of scope: no email or SMS notifications for now. Keep the code simple and clean, make every page responsive and mobile-first, and use the same component structure (components/ui, components/admin, livewire/citizen, livewire/admin) as the reference project.



Build a public chatbot for the Overseas Judiciary Complaint Portal, modelled on the reference project's chatbot: app/Livewire/Public/ComplaintChatbot.php, resources/js/chatbot.js and the ChatbotQuestion, ChatConversation and ChatMessage models. Use the same stack: Laravel 12, Livewire 3, Alpine.js, Tailwind v4. It is scripted and rule-based, with no AI API.

Launcher: a floating chat button sits on every public page and opens a modern chat window, full screen on mobile. It shows a welcome teaser and remembers its open/started state in sessionStorage. Bot messages appear one at a time with a "typing…" indicator and words streaming in. Sending stays disabled while the bot is "speaking". Respect reduced motion.

Main menu: quick-reply buttons for File a Complaint, Track My Complaint, How It Works / FAQs and Contact Information. Typing in the input narrows the quick replies.

Filing: the bot asks one question at a time: name, country of residence, phone (WhatsApp), CNIC/passport number, court/district, case number, case title, complaint category, description and optional attachments. It validates each answer with the same rules as the complaint form and lets the citizen skip optional questions ("skip", "no", "ںیہن"). At the end it shows a summary where any answer can be edited, then registers the complaint through the same service as the form and returns the tracking number.

Tracking: the citizen enters a tracking number plus their CNIC/passport. The bot shows the complaint's status and the admin's latest review.

Bilingual: English and Urdu (Noto Nastaliq Urdu, RTL), following the site's locale switcher.

Admin side (single Admin role): admins manage the chatbot's questions (add, edit, reorder, activate/deactivate, wording in both English and Urdu) and the FAQ answers. They can view chat conversation logs, including unfinished chats.

Other requirements: rate-limit the chatbot. No email or SMS for now.

