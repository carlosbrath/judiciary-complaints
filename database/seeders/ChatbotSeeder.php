<?php

namespace Database\Seeders;

use App\Models\ChatbotFaq;
use App\Models\ChatbotQuestion;
use Illuminate\Database\Seeder;

/** Core chatbot questions and starter FAQs. Existing rows (possibly reworded by the admin) are kept. */
class ChatbotSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ChatbotQuestion::defaults() as $i => $question) {
            ChatbotQuestion::query()->firstOrCreate(['key' => $question['key']], [
                ...$question,
                'is_system' => true,
                'is_active' => true,
                'sort_order' => ($i + 1) * 10,
            ]);
        }

        if (ChatbotFaq::query()->exists()) {
            return;
        }

        foreach ($this->faqs() as $i => $faq) {
            ChatbotFaq::query()->create([...$faq, 'is_active' => true, 'sort_order' => ($i + 1) * 10]);
        }
    }

    private function faqs(): array
    {
        return [
            [
                'question_en' => 'How do I file a complaint?',
                'question_ur' => 'میں شکایت کیسے درج کروں؟',
                'answer_en' => "You can file it right here in this chat — choose \"File a Complaint\" and answer a few questions. Or create an account on the portal and use the online form.\n\nYou will need the court, the case number and title, and a short description of the problem. You get a tracking number as soon as it is registered.",
                'answer_ur' => "آپ یہیں اس چیٹ میں شکایت درج کر سکتے ہیں — \"شکایت درج کریں\" منتخب کریں اور چند سوالوں کے جواب دیں۔ یا پورٹل پر اکاؤنٹ بنا کر آن لائن فارم استعمال کریں۔\n\nآپ کو عدالت، مقدمہ نمبر و عنوان اور مسئلے کی مختصر تفصیل درکار ہوگی۔ شکایت درج ہوتے ہی آپ کو ٹریکنگ نمبر مل جائے گا۔",
                'keywords' => 'file, register, submit, new complaint, how, درج, شکایت, darj, shikayat',
            ],
            [
                'question_en' => 'Who can use this portal?',
                'question_ur' => 'یہ پورٹل کون استعمال کر سکتا ہے؟',
                'answer_en' => 'Citizens of Azad Jammu & Kashmir living abroad who have a case — or faced a problem during proceedings — in a court of the lower judiciary of AJ&K (district, sessions, civil and family courts).',
                'answer_ur' => 'بیرونِ ملک مقیم آزاد جموں و کشمیر کے شہری جن کا مقدمہ ماتحت عدلیہ کی کسی عدالت (ضلعی، سیشن، سول یا فیملی عدالت) میں ہو یا جنہیں کارروائی کے دوران کوئی مسئلہ پیش آیا ہو۔',
                'keywords' => 'who, eligible, overseas, abroad, کون, بیرون',
            ],
            [
                'question_en' => 'What can I complain about?',
                'question_ur' => 'میں کس بارے میں شکایت کر سکتا ہوں؟',
                'answer_en' => 'Delays in proceedings, repeated adjournments, the conduct of court staff, corruption or bribery, certified copies or records not issued, summons not served, decrees not executed — or any other problem you faced in a court case.',
                'answer_ur' => 'کارروائی میں تاخیر، بار بار التوا، عدالتی عملے کا رویہ، بدعنوانی یا رشوت، نقول یا ریکارڈ کا اجرا نہ ہونا، سمن کی تعمیل نہ ہونا، ڈگری پر عمل درآمد نہ ہونا — یا مقدمے میں پیش آنے والا کوئی بھی مسئلہ۔',
                'keywords' => 'category, type, kind, about, delay, bribery, اقسام, تاخیر',
            ],
            [
                'question_en' => 'How do I check the status of my complaint?',
                'question_ur' => 'میں اپنی شکایت کی صورتحال کیسے دیکھوں؟',
                'answer_en' => 'Choose "Track My Complaint" in this chat and enter your tracking number and CNIC/passport number. If you have an account, log in to see the full status timeline and every remark from the court administration.',
                'answer_ur' => 'اس چیٹ میں "اپنی شکایت ٹریک کریں" منتخب کریں اور ٹریکنگ نمبر اور شناختی کارڈ / پاسپورٹ نمبر لکھیں۔ اگر آپ کا اکاؤنٹ ہے تو لاگ اِن کر کے مکمل ٹائم لائن اور عدالتی انتظامیہ کے تمام ریمارکس دیکھیں۔',
                'keywords' => 'status, track, check, progress, tracking, ٹریک, صورتحال',
            ],
            [
                'question_en' => 'How long does it take?',
                'question_ur' => 'اس میں کتنا وقت لگتا ہے؟',
                'answer_en' => 'Every complaint is reviewed by the court administration. Most are taken up within a few working days; you can follow each step — Submitted, Under Review, In Progress, Resolved — online.',
                'answer_ur' => 'ہر شکایت کا جائزہ عدالتی انتظامیہ لیتی ہے۔ زیادہ تر شکایات پر چند دنوں میں کارروائی شروع ہو جاتی ہے؛ آپ ہر مرحلہ — جمع شدہ، زیرِ جائزہ، زیرِ کارروائی، حل شدہ — آن لائن دیکھ سکتے ہیں۔',
                'keywords' => 'time, long, days, when, کتنا, وقت',
            ],
            [
                'question_en' => 'Does it cost anything?',
                'question_ur' => 'کیا اس کی کوئی فیس ہے؟',
                'answer_en' => 'No. Filing and tracking a complaint is completely free.',
                'answer_ur' => 'نہیں۔ شکایت درج کرنا اور ٹریک کرنا بالکل مفت ہے۔',
                'keywords' => 'fee, cost, free, pay, charge, فیس, مفت',
            ],
        ];
    }
}
