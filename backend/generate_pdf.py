import os
import sys
import subprocess

# Ensure fpdf2, arabic-reshaper, and python-bidi are installed
def install_requirements():
    try:
        import fpdf
        import arabic_reshaper
        from bidi.algorithm import get_display
        print("Required libraries are already installed.")
    except ImportError:
        print("Installing required libraries (fpdf2, arabic-reshaper, python-bidi)...")
        # Run pip install
        subprocess.check_call([sys.executable, "-m", "pip", "install", "fpdf2", "arabic-reshaper", "python-bidi"])

# Install dependencies if not present
install_requirements()

from fpdf import FPDF
import arabic_reshaper
from bidi.algorithm import get_display

class ArabicPDF(FPDF):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.is_cover_page = True

    def header(self):
        if self.is_cover_page:
            return
            
        # Draw Gold Border
        self.set_draw_color(202, 163, 74) # Gold
        self.set_line_width(1.0)
        self.rect(5, 5, self.w - 10, self.h - 10)
        
        # Header text
        self.set_font("ArialArabic", "B", 10)
        self.set_text_color(120, 120, 120)
        
        # Left header text (logo name)
        logo_text = get_display(arabic_reshaper.reshape("جوهرة | منصة المزادات الفاخرة"))
        self.cell(0, 5, logo_text, 0, 0, "L")
        
        # Right header text (section info)
        sec_text = get_display(arabic_reshaper.reshape("دليل النظام والواجهات الشامل"))
        # To align properly in RTL/LTR header, we can use absolute positioning or cell
        self.set_x(self.w - 85)
        self.cell(70, 5, sec_text, 0, 1, "R")
        
        # Draw thin separator line below header
        self.set_draw_color(230, 230, 230)
        self.line(10, 15, self.w - 10, 15)
        self.ln(10)

    def footer(self):
        if self.is_cover_page:
            return
            
        # Draw thin separator line above footer
        self.set_draw_color(230, 230, 230)
        self.line(10, self.h - 15, self.w - 10, self.h - 15)
        
        self.set_y(-12)
        self.set_font("ArialArabic", "", 9)
        self.set_text_color(150, 150, 150)
        
        # Arabic page number formatting
        text = get_display(arabic_reshaper.reshape("منصة جوهرة - التوثيق الفني - صفحة"))
        self.cell(0, 10, f"{text} {self.page_no()}", 0, 0, "C")

def reshape_text(txt):
    return get_display(arabic_reshaper.reshape(txt))

def draw_arabic_paragraph(pdf, text, width, line_height=7, align='R'):
    paragraphs = text.split("\n")
    for para in paragraphs:
        if not para.strip():
            pdf.ln(line_height / 2)
            continue
        words = para.split(" ")
        current_line = ""
        for word in words:
            test_line = (current_line + " " + word).strip() if current_line else word
            shaped_test = get_display(arabic_reshaper.reshape(test_line))
            if pdf.get_string_width(shaped_test) > width:
                # Print current line
                shaped_line = get_display(arabic_reshaper.reshape(current_line))
                pdf.cell(width, line_height, shaped_line, 0, 1, align)
                current_line = word
            else:
                current_line = test_line
        if current_line:
            shaped_line = get_display(arabic_reshaper.reshape(current_line))
            pdf.cell(width, line_height, shaped_line, 0, 1, align)

def main():
    pdf = ArabicPDF(orientation="P", unit="mm", format="A4")
    
    # Register Arial font from Windows Fonts directory which has Arabic glyphs
    font_path = "C:\\Windows\\Fonts\\arial.ttf"
    font_bold_path = "C:\\Windows\\Fonts\\arialbd.ttf"
    
    if not os.path.exists(font_path):
        # Fallback if Windows Fonts is elsewhere
        font_path = "arial.ttf"
        font_bold_path = "arialbd.ttf"
        
    pdf.add_font("ArialArabic", "", font_path)
    pdf.add_font("ArialArabic", "B", font_bold_path)
    
    pdf.set_margins(15, 20, 15)
    pdf.add_page()
    
    # ------------------ COVER PAGE ------------------
    pdf.is_cover_page = True
    
    # Background color - Dark Charcoal
    pdf.set_fill_color(26, 26, 26)
    pdf.rect(0, 0, pdf.w, pdf.h, "F")
    
    # Gold decorative border
    pdf.set_draw_color(202, 163, 74)
    pdf.set_line_width(2.0)
    pdf.rect(8, 8, pdf.w - 16, pdf.h - 16, "D")
    
    pdf.set_line_width(0.5)
    pdf.rect(10, 10, pdf.w - 20, pdf.h - 20, "D")
    
    pdf.ln(40)
    
    # Platform Title
    pdf.set_font("ArialArabic", "B", 34)
    pdf.set_text_color(202, 163, 74) # Gold
    title = get_display(arabic_reshaper.reshape("مـنـصـة جـوهـرة"))
    pdf.cell(0, 15, title, 0, 1, "C")
    
    pdf.set_font("ArialArabic", "", 16)
    pdf.set_text_color(180, 180, 180)
    subtitle1 = get_display(arabic_reshaper.reshape("المزاد الرقمي الفاخر للأحجار الكريمة النادرة"))
    pdf.cell(0, 12, subtitle1, 0, 1, "C")
    
    pdf.ln(15)
    
    # Line Separator
    pdf.set_draw_color(202, 163, 74)
    pdf.line(pdf.w / 2 - 40, pdf.y, pdf.w / 2 + 40, pdf.y)
    pdf.ln(20)
    
    # Document Title
    pdf.set_font("ArialArabic", "B", 22)
    pdf.set_text_color(255, 255, 255)
    doc_title = get_display(arabic_reshaper.reshape("دليل النظام الشامل وتوثيق الواجهات"))
    pdf.cell(0, 12, doc_title, 0, 1, "C")
    
    pdf.set_font("ArialArabic", "", 12)
    pdf.set_text_color(180, 180, 180)
    doc_subtitle = get_display(arabic_reshaper.reshape("دليل تقني وإداري مفصل لجميع واجهات المستخدمين والدورات البرمجية"))
    pdf.cell(0, 10, doc_subtitle, 0, 1, "C")
    
    pdf.ln(50)
    
    # Metadata info at the bottom
    pdf.set_font("ArialArabic", "", 11)
    pdf.set_text_color(202, 163, 74)
    info1 = get_display(arabic_reshaper.reshape("إعداد وتطوير: فريق هندسة البرمجيات"))
    pdf.cell(0, 8, info1, 0, 1, "C")
    
    pdf.set_font("ArialArabic", "", 10)
    pdf.set_text_color(140, 140, 140)
    info2 = get_display(arabic_reshaper.reshape("نسخة التوثيق: 1.0.0  |  تاريخ الإصدار: يونيو 2026"))
    pdf.cell(0, 8, info2, 0, 1, "C")
    
    # ------------------ END OF COVER ------------------
    
    # Start Content Pages
    pdf.is_cover_page = False
    pdf.add_page()
    
    # Reset text colors for body pages
    pdf.set_text_color(40, 40, 40)
    
    sections = [
        {
            "num": "1",
            "title": "مقدمة ونظرة عامة على النظام",
            "content": (
                "تعد منصة \"جوهرة\" (Jawhara) نموذجاً رائداً لمنصات التجارة الرقمية المتخصصة في تداول ومزايدة الأحجار الكريمة النادرة والفخمة. "
                "تم تصميم وتطوير المنصة لتلبي احتياجات فئتين رئيسيتين من المستخدمين: الهواة والمشترين الباحثين عن اقتناء قطع حصرية، "
                "والبائعين المحترفين وأصحاب المحلات التجارية الراغبين في عرض منتجاتهم في سوق آمن وتنافسي.\n\n"
                "تدمج المنصة بين أسلوبين للتجارة الرقمية:\n"
                "1. الشراء المباشر: حيث يمكن للمشتري اختيار حجر كريم وشرائه مباشرة بالاستناد إلى السعر الثابت المحدد من قبل البائع.\n"
                "2. نظام المزادات الحية والتنافسية: وهو القلب النابض للمنصة، حيث تطرح الأحجار في مزادات علنية محددة المدة الزمنية، "
                "ويتنافس المشترون لتقديم أعلى سعر، مما يتيح تحديد القيمة العادلة للأحجار النادرة بناءً على آلية العرض والطلب.\n\n"
                "تعتمد المنصة تصميماً بصرياً يفيض بالفخامة، حيث يطغى الطابع الملكي ذو الألوان الداكنة (Charcoal) واللمسات الذهبية (Gold)، "
                "مما يمنح المستخدمين انطباعاً فورياً بالأمان والموثوقية الرفيعة التي تليق بتداول سلع ذات قيمة مالية عالية."
            )
        },
        {
            "num": "2",
            "title": "البنية البرمجية والتقنية للمنصة",
            "content": (
                "تم بناء وتطوير منصة جوهرة بالاعتماد على بنية هيكلية صلبة تضمن سرعة الاستجابة والأمان الفائق للعمليات المالية والمزايدات:\n\n"
                "• إطار العمل الخلفي (Backend Framework): Laravel 11.x\n"
                "تم اختيار الإصدار الأحدث من Laravel للاستفادة من الميزات المتطورة في إدارة التوجيه (Routing)، والمصادقة الآمنة (Authentication)، "
                "وأنظمة الأمان والتحقق من الصلاحيات الذكية، وحماية الثغرات الشائعة مثل حقن البيانات والـ CSRF.\n\n"
                "• قاعدة البيانات (Database Engine): SQLite\n"
                "تستخدم قاعدة بيانات SQLite محلية مدمجة وسريعة الاستجابة، تم ضبطها وتغذيتها ببيانات اختبارية واقعية (Seeders) تمثل الأحجار الكريمة، "
                "والمستندات الرسمية، وسجلات المزايدات، لتسهيل عمليات الفحص والتطوير وضمان استقلالية البيانات.\n\n"
                "• الواجهات الأمامية والتحريك الديناميكي (Frontend Technology):\n"
                "تعتمد الواجهات على لغة التنسيق Tailwind CSS متكاملة مع Vanilla CSS مخصص لبناء التأثيرات الذهبية المتوهجة والتفاعلات الدقيقة. "
                "أما بالنسبة للمنطق البرمجي للواجهات، فيتم استخدام Vanilla JavaScript للتخاطب المباشر مع واجهات برمجة التطبيقات (APIs) لمعالجة المزايدات وتحديث رصيد المحفظة افتراضياً بشكل فوري ولحظي."
            )
        },
        {
            "num": "3",
            "title": "هيكلية قاعدة البيانات والكيانات الرئيسية",
            "content": (
                "تترابط كيانات قاعدة البيانات في نظام جوهرة بشكل وثيق لضمان اتساق العمليات ومنع تعارض الصفقات، وتتمثل الجداول الرئيسية بالتالي:\n\n"
                "1. جدول المستخدمين (Users):\n"
                "يحفظ بيانات الحسابات الشخصية (الاسم، البريد الإلكتروني، كلمة المرور المشفرة، الدور: مشتري أو بائع، الرصيد المالي المتاح، والرصيد المحجوز كضمان، وحالة الحساب).\n\n"
                "2. جدول المنتجات (Products):\n"
                "يخزن كافة المواصفات الجيولوجية والفنية لكل حجر كريم (نوع الحجر، الوزن بالقيراط، درجة النقاء، جودة القطع، بلد المنشأ، رقم شهادة GIA، الصور، والسعر المباشر، وهوية البائع المالك).\n\n"
                "3. جدول المزادات (Auctions):\n"
                "يدير جلسات المزايدة النشطة والجديدة (المنتج المستهدف، سعر البداية، السعر الحالي، الحد الأدنى للزيادة، وقت البدء والانتهاء، والحالة: نشط/معلق/منتهي).\n\n"
                "4. جدول المزايدات (Bids):\n"
                "يسجل كافة حركات المزايدة التي يجريها المشترون بقيمها الزمنية وقيمها المالية لتحديد الفائز النهائي بدقة بالغة.\n\n"
                "5. جدول الطلبات (Orders):\n"
                "يحتفظ بسجلات عمليات الشراء المباشر والعمليات المالية المكتملة وحالة شحن الأحجار الكريمة."
            )
        },
        {
            "num": "4",
            "title": "أدوار وصلاحيات مستخدمي المنصة",
            "content": (
                "يقسم النظام مستخدميه إلى ثلاثة أدوار رئيسية بصلاحيات أمان صارمة:\n\n"
                "أ. المشتري (Buyer):\n"
                "• يحصل تلقائياً عند التسجيل على رصيد ترحيبي قدره 100,000 ريال سعودي في محفظته الافتراضية.\n"
                "• تصفح المتجر والمزادات واستكشاف المواصفات وشهادات الفحص الفنية.\n"
                "• تقديم المزايدات والشراء المباشر مع تجميد فوري لنسبة الضمان المالي 5%.\n"
                "• تتبع طلباته الخاصة وحالة توصيل الأحجار الكريمة المشتراة.\n\n"
                "ب. البائع (Seller):\n"
                "• يسجل كحساب معلق ويتطلب موافقة وتفعيل من قبل مدير المنصة ليبدأ البيع.\n"
                "• إضافة أحجار كريمة جديدة بخصائصها الفنية وشهادات التوثيق الخاصة بها.\n"
                "• طرح المنتجات للمزاد العلني وتحديد المعايير المالية لبدء المزاد.\n"
                "• تتبع الطلبات الواردة لمنتجاته وتأكيد الشحن ومراجعة إحصائيات أرباحه السنوية والشهرية.\n\n"
                "ج. المدير / المسؤول (Admin):\n"
                "• الصلاحيات المطلقة لمراقبة كافة أنشطة المنصة من خلال شاشة إحصائية موحدة.\n"
                "• فحص وتفعيل حسابات البائعين المعلقين بعد التحقق من أوراقهم الثبوتية.\n"
                "• الموافقة على المزادات المقترحة من البائعين قبل إطلاقها للجمهور لمنع العروض المخالفة.\n"
                "• إدارة حسابات الأعضاء وحظر الحسابات المتلاعبة أو تعديل الأرصدة يدوياً.\n"
                "• ضبط إعدادات المنصة وقيم العمولات ونسب التأمين ونظام الأمان المالي."
            )
        },
        {
            "num": "5",
            "title": "دورات العمل وتدفق العمليات البرمجية",
            "content": (
                "يدير النظام دورتين رئيسيتين للأعمال تتميزان بالسلاسة والأمان المتناهي:\n\n"
                "1. دورة المزايدات والضمان المالي (Bidding & Escrow Flow):\n"
                "• يبدأ المشتري باختيار مزاد نشط، وعند رغبته في المزايدة، يتحقق النظام أولاً من توفر رصيد كافٍ يغطي قيمة العرض بالإضافة إلى 5% كتأمين جدية.\n"
                "• يتم حجز قيمة الـ 5% فوراً في خانة \"الرصيد المحجوز\" (Locked Balance) لضمان جدية المزايد وتجنب العروض الوهمية.\n"
                "• في حال قيام مزايد آخر بتقديم عرض أعلى، يقوم النظام تلقائياً وبشكل فوري بإلغاء حجز مبلغ التأمين وإعادته للرصيد المتاح للمزايد السابق دون أي تأخير.\n"
                "• عند انتهاء وقت المزاد، يتم إعلان المزايد صاحب العرض الأعلى فائزاً، ويتم تحويل حالة المزاد إلى منتهي، وتثبيت العملية لصالح البائع بعد حسم عمولة المنصة.\n\n"
                "2. دورة البيع والشحن للمنتجات الثابتة:\n"
                "• يقوم المشتري بالنقر على \"شراء الآن\" لمنتج معروض في المتجر.\n"
                "• يخصم سعر المنتج مباشرة من المحفظة وتتحول العملية لصفحة \"الطلبات الواردة\" لدى البائع.\n"
                "• يلتزم البائع بتجهيز الحجر الكريم وإرفاق مستندات الشحن الفعلي، وتحديث حالة الطلب إلى \"تم الشحن\".\n"
                "• بمجرد استلام المشتري للمنتج وتأكيده في النظام، يتم تحرير المبلغ المالي وإيداعه في محفظة البائع مع إمكانية سحبه."
            )
        },
        {
            "num": "6",
            "title": "دليل تفصيلي لجميع واجهات النظام",
            "content": (
                "يحتوي النظام على مجموعة متكاملة من الواجهات المصممة بدقة لتلبية احتياجات كافة فئات المستخدمين:\n\n"
                "6.1 الواجهات العامة للموقع (Public & Buyer Viewports):\n"
                "• الصفحة الرئيسية (Home Page - welcome.blade.php):\n"
                "تتميز بشريط تفاعلي علوي متحرك يعرض أسعار المعادن والأحجار الكريمة العالمية لحظة بلحظة. تحتوي على قسم رئيسي ترحيبي عالي الجودة لعرض أهداف المنصة، متبوعاً بقسم المزادات الجارية الأكثر جذباً للمشترين والأحجار المضافة حديثاً.\n\n"
                "• صفحة المتجر الشامل (The Shop Page - shop.blade.php):\n"
                "تتيح للمشترين استكشاف كامل المخزون المتوفر للشراء المباشر. تحتوي على شريط تصفية دائري تفاعلي للأقسام (ألماس، ياقوت، زمرد، عقيق) يضيء باللون الذهبي البارز مع فلترة فورية للمنتجات باستخدام الجافا سكريبت دون الحاجة لإعادة تحميل الصفحة. كما تضم فلترة جانبية مرنة للمدى السعري ومصدر الحجر الكريم.\n\n"
                "• صفحة المزادات (Auctions Page - auction.blade.php):\n"
                "تعرض جميع المزادات الجارية مرتبة حسب تاريخ الانتهاء، مع عرض عداد تنازلي حركي ملون لكل مزاد يوضح الوقت المتبقي بالساعات والدقائق والثواني.\n\n"
                "• صفحة تفاصيل المزاد (Auction Details Page - auction-details.blade.php):\n"
                "الواجهة الأكثر تفاعلية بالمنصة. تتضمن لوحة المزايدة الحية، وسجل حركات المزايدة المرتب تنازلياً، ونافذة منبثقة تفاعلية لعرض شهادة فحص الأحجار الكريمة الصادرة عن مختبر GIA الفيدرالي بمجرد النقر على ختم الفحص المائي. كما تتميز بتأثير وميض ذهبي متوهج للسعر عند تحديثه وتفعيل صوت مطرقة المزاد الفاخرة.\n\n"
                "• واجهات المصادقة (Auth Pages):\n"
                "نماذج تسجيل الدخول (login.blade.php) وإنشاء حساب جديد (register.blade.php) تتيح للمستخدم اختيار صفته (مشتري أو بائع) بأسلوب تصميم عصري ومنسق مع حقول الأمان المشفرة."
            )
        },
        {
            "num": "6.2",
            "title": "واجهات لوحة تحكم البائعين (Seller Viewports)",
            "content": (
                "تمثل مركز عمليات التاجر لإدارة متجره ومنتجاته، وتشتمل على الواجهات التالية:\n\n"
                "• لوحة التحكم الرئيسية (Seller Dashboard - dashboard.blade.php):\n"
                "تعرض ملخصاً إحصائياً بيانياً لأداء المتجر مثل إجمالي المبيعات، والأرباح الحالية القابلة للسحب، والمنتجات النشطة في المتجر، وتنبيهات المزادات الجارية.\n\n"
                "• إدارة المنتجات (Products View - products.blade.php):\n"
                "جدول منظم يعرض جميع المنتجات المملوكة للبائع وحالتها (متوفر، محجوز بمزاد، مباع) مع إمكانية التعديل السريع أو الإيقاف.\n\n"
                "• إضافة منتج جديد (Add Product - add-product.blade.php):\n"
                "نموذج تفاعلي مقسم لخطوات، يتيح للبائع إدخال مواصفات الحجر الكريم بدقة (الوزن بالقيراط، نوع القطع، درجة النقاء واللون، رقم شهادة الفحص الموثقة، السعر المطلوب، وتحميل صور الحجر عالية الدقة).\n\n"
                "• إدارة وبدء المزادات (Auctions Control - auctions.blade.php & create-auction.blade.php):\n"
                "تسمح للبائع باختيار أحد منتجاته المتوفرة وتحديد سعر البداية للمزاد وقيمة الزيادة الصغرى المطلوبة وتاريخي البدء والانتهاء، ثم إرسال الطلب للاعتماد من الإدارة.\n\n"
                "• إدارة الأرباح والطلبات (Earnings & Orders - earnings.blade.php & orders.blade.php):\n"
                "متابعة الطلبات الموجهة لمنتجات البائع، وتحديث حالة شحن الأحجار الكريمة، ومتابعة استلام الأموال المحررة في محفظة الأرباح وطلب سحبها للرصيد الفعلي."
            )
        },
        {
            "num": "6.3",
            "title": "واجهات لوحة تحكم الإدارة (Admin Viewports)",
            "content": (
                "تمثل شاشة الرقابة والموافقة العليا والتحكم الكامل بالمنصة، وتضم:\n\n"
                "• لوحة الإدارة الرئيسية (Admin Dashboard - dashboard.blade.php):\n"
                "تعرض بطاقات إحصائية ملونة توضح إجمالي عدد المستخدمين بالمنصة، وإجمالي حجم الصفقات والمبيعات الجارية، والعمولات المستحقة للمنصة، والمبيعات اليومية.\n\n"
                "• البائعون المعلقون (Pending Sellers - sellers.blade.php):\n"
                "شاشة تدرج طلبات البائعين الجدد الذين سجلوا بالمنصة ولم يتم تفعيلهم بعد. يستعرض المدير بياناتهم وبطاقاتهم الضريبية أو هوياتهم مع أزرار تفعيل سريعة (قبول / رفض) بنقرة واحدة.\n\n"
                "• الطلبات العامة والمبيعات (Global Orders - orders.blade.php):\n"
                "شاشة لمراقبة جميع عمليات الشراء والمبادلات المالية التي تتم بالمنصة، لضمان حماية المالي ومكافحة الاحتيال والتدخل لفض أي نزاع مالي بين البائع والمشتري.\n\n"
                "• الموافقة على المزادات (Auctions Approval - auctions.blade.php):\n"
                "جدول بالطلبات المقدمة من البائعين لإنشاء مزادات جديدة. يفحص المدير تفاصيل المزاد والمنتج المربوط وسعر البداية، ويقوم بالموافقة عليه لينطلق المزاد تلقائياً في وقته المحدد للجمهور.\n\n"
                "• التحكم بالمستخدمين وتصنيف الأحجار (Users & Categories - users.blade.php & categories.blade.php):\n"
                "تتيح للمدير إدارة حسابات العملاء وتعديل الأرصدة يدوياً للمحفظة للظروف الاستثنائية، وإضافة فئات جديدة للأحجار الكريمة لتظهر في فلاتر التصفية بالمتجر.\n\n"
                "• إعدادات النظام العامة (System Settings - settings.blade.php):\n"
                "لتخصيص عمولة المنصة الافتراضية، وضبط معايير الأمان وقيمة الحد الأدنى لتأمين المزادات وغيرها من الثوابت الهامة للموقع."
            )
        },
        {
            "num": "7",
            "title": "نظام الأمان والضمان المالي الفاخر",
            "content": (
                "تعتمد منصة جوهرة على تطبيق أعلى معايير الحماية لضمان تجربة مستخدم موثوقة وخالية من المخاطر:\n\n"
                "• شهادة الأحجار الكريمة التفاعلية (Watermarked GIA Certificates):\n"
                "للقضاء على عمليات التزييف، يتم التحقق برمجياً من شهادات الفحص للأحجار. بمجرد نقر المشتري على ختم الفحص المائي في تفاصيل المزاد، تظهر له لوحة منبثقة فاخرة تعرض شهادة التوثيق الفنية للأحجار الكريمة الصادرة من مختبر GIA الفيدرالي بمواصفات مطابقة 100% لبيانات قاعدة البيانات لضمان عدم التلاعب.\n\n"
                "• نظام حماية وضمان المزايدة (Bid Security Deposit):\n"
                "يتطلب الاشتراك في أي مزاد وجود تأمين مالي بنسبة 5% يتم حجزها مؤقتاً من محفظة المزايد. تمنع هذه الآلية المزايدات العشوائية أو محاولات التلاعب بالأسعار من قبل حسابات وهمية. عند دخول مزايد جديد بسعر أعلى، يعيد النظام تلقائياً وبشكل فوري كامل مبلغ التأمين المحجوز إلى حساب المزايد السابق ليكون متاحاً للاستخدام الفوري.\n\n"
                "• تشفير العمليات والـ API الموحد:\n"
                "تجري كافة عمليات المزايدة والشراء المباشر عبر خادم Laravel موحد يتصل بالـ API بشكل مشفر وآمن، مما يلغي تماماً مخاطر تعارض العمليات أو حدوث هجمات الاختراق وحقن البيانات في قواعد البيانات."
            )
        },
        {
            "num": "8",
            "title": "خاتمة وتوصيات التشغيل الفني للمنصة",
            "content": (
                "تمثل منصة جوهرة تجسيداً للتطوير البرمجي الاحترافي الذي يجمع بين جمالية التصميم الفاخر وقوة البناء الهيكلي والأمان المالي.\n\n"
                "توصيات التشغيل والتحسين المستقبلي:\n"
                "1. التحول إلى خادم قواعد بيانات مركزي مثل MySQL أو PostgreSQL عند إطلاق المنصة فعلياً للمستخدمين لزيادة كفاءة معالجة البيانات المتزامنة بكثافة عالية.\n"
                "2. دمج بوابات دفع إلكترونية حقيقية (مثل Stripe أو HyperPay) لاستبدال نظام المحفظة الافتراضية بنظام شحن وسحب أموال حقيقي خاضع لقوانين التجارة المحلية.\n"
                "3. إضافة نظام إشعارات فورية عبر تقنية WebSockets (مثل Laravel Reverb أو Pusher) لتحديث المزايدات والعد التنازلي بشكل تفاعلي تام دون الحاجة لتحديث الواجهة يدوياً.\n"
                "4. إتاحة تطبيق للهواتف الذكية (iOS & Android) يعتمد على نفس واجهات برمجة التطبيقات (APIs) الحالية لتوسيع قاعدة العملاء وسهولة الوصول للمزادات."
            )
        }
    ]
    
    # Render sections
    for sec in sections:
        # Title of section
        pdf.set_font("ArialArabic", "B", 15)
        pdf.set_text_color(202, 163, 74) # Gold
        
        sec_num_title = f"{sec['num']}. {sec['title']}"
        pdf.cell(0, 10, reshape_text(sec_num_title), 0, 1, "R")
        
        # Gold underline for title
        pdf.set_draw_color(202, 163, 74)
        pdf.set_line_width(0.8)
        pdf.line(pdf.w - 15, pdf.y, pdf.w - 100, pdf.y)
        pdf.ln(5)
        
        # Body content of section
        pdf.set_font("ArialArabic", "", 11)
        pdf.set_text_color(40, 40, 40)
        
        draw_arabic_paragraph(pdf, sec['content'], pdf.w - 30, line_height=7, align='R')
        pdf.ln(12)
        
    output_filename = "jawhara_documentation.pdf"
    pdf.output(output_filename)
    print(f"PDF documentation successfully generated as '{output_filename}'!")

if __name__ == "__main__":
    main()
