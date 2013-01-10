<?php
/**
 * Internationalisation file for FlaggedRevs extension, section ReviewedPages
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

$messages['en'] = array(
	'reviewedpages' => 'Reviewed pages',
	'reviewedpages-leg' => 'List pages that have been reviewed',
	'reviewedpages-list' => 'This lists contains [[{{MediaWiki:Validationpage}}|reviewed]] pages whose \'\'highest attained\'\' review level (of a revision) is the specified level.
	A link is given to the latest revision of that level.',
	'reviewedpages-none' => 'There are no pages in this list.',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'reviewed versions',
	'reviewedpages-best' => 'best revision',
);

/** Message documentation (Message documentation)
 * @author Aaron Schulz
 * @author Jon Harald Søby
 * @author SPQRobin
 * @author Umherirrender
 */
$messages['qqq'] = array(
	'reviewedpages' => '{{Flagged Revs}}
Name of the Special:ReviewedPages page, which lists out pages that have a stable version.',
	'reviewedpages-leg' => '{{Flagged Revs}}
Shown on Special:ReviewedPages.',
	'reviewedpages-list' => '{{Flagged Revs}}
Shown on Special:ReviewedPages.
Note that the "highest attained" language is key. If the user viewing the special page specifies "checked", then "quality" pages will not appear.
Parameter $1 is the number of reviewed pages for use with PLURAL.',
	'reviewedpages-none' => '{{Flagged Revs}}
Shown on Special:ReviewedPages.
{{Identical|There are no pages in this list}}',
	'reviewedpages-lev-0' => '{{Flagged Revs}}
{{optional}}',
	'reviewedpages-lev-1' => '{{Flagged Revs}}
{{optional}}',
	'reviewedpages-lev-2' => '{{Flagged Revs}}
{{optional}}
{{Identical|Featured}}',
	'reviewedpages-all' => '{{Flagged Revs}}
Shown on Special:ReviewedPages. Links to Special:ReviewedVersions for a particular page.',
	'reviewedpages-best' => '{{Flagged Revs}}
Shown on Special:ReviewedPages. Links to ?stableid=best for a particular page. This gives the newest of the highest rated page revision.',
);

/** Afrikaans (Afrikaans)
 * @author Arnobarnard
 * @author Deadelf
 * @author Naudefj
 */
$messages['af'] = array(
	'reviewedpages' => 'Gekontroleerde bladsye',
	'reviewedpages-leg' => 'Lys met bladsye wat gekontrolleer is',
	'reviewedpages-list' => "Hierdie lyste bevat [[{{MediaWiki:Validationpage}}|hersiende]] bladsye wat se ''hoogste'' wysigingsvlak (van 'n weergawe) op die gespesifiseerde vlak is.
'n Skakel word voorsien na die mees onlangste wysiging op daardie vlak.",
	'reviewedpages-none' => 'Daar is geen bladsye in hierdie lys nie.',
	'reviewedpages-lev-2' => 'Uitgelig',
	'reviewedpages-all' => 'gekontroleerde weergawes',
	'reviewedpages-best' => 'beste weergawe',
);

/** Aragonese (aragonés)
 * @author Juanpabl
 */
$messages['an'] = array(
	'reviewedpages' => 'Pachinas revisatas',
	'reviewedpages-leg' => "Lista de pachinas que s'han revisato",
	'reviewedpages-list' => "As siguients pachinas s'han revisato dica o libel especificato
Ista lista contiene pachinas [[{{MediaWiki:Validationpage}}|revisadas]] que o suyo livel de revisión ''más alto''  (de una revisión) ye o livel especificato. 
Se da un vinclo t'a zaguera versión d'iste livel.",
	'reviewedpages-none' => 'No bi ha garra pachina en ista lista',
	'reviewedpages-lev-0' => 'Supervisato',
	'reviewedpages-lev-1' => 'Qualidat',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'versions revisatas',
	'reviewedpages-best' => 'millor versión',
);

/** Arabic (العربية)
 * @author ;Hiba;1
 * @author Meno25
 * @author OsamaK
 */
$messages['ar'] = array(
	'reviewedpages' => 'صفحات مراجعة',
	'reviewedpages-leg' => 'عرض الصفحات التي تمت مراجعتها',
	'reviewedpages-list' => "هذه القائمة تحتوي على الصفحات [[{{MediaWiki:Validationpage}}|المراجعة]] التي مستوى المراجعة ''الأعلى'' (لمراجعة) هو المستوى المحدد.
	وصلة معطاة لآخر مراجعة لهذا المستوى.",
	'reviewedpages-none' => 'لا توجد صفحات في هذه القائمة',
	'reviewedpages-lev-0' => 'منظورة',
	'reviewedpages-lev-1' => 'جودة',
	'reviewedpages-lev-2' => 'مختارة',
	'reviewedpages-all' => 'نسخ مراجعة',
	'reviewedpages-best' => 'افضل مراجعة',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Meno25
 */
$messages['arz'] = array(
	'reviewedpages' => 'صفحات مراجعة',
	'reviewedpages-leg' => 'اعرض الصفحات حسب أعلى مستوى مراجعة', # Fuzzy
	'reviewedpages-list' => 'هذه الصفحه تعرض الصفحات التى تمت مراجعتها (على أقصى تقدير) للمستوى المحدد.', # Fuzzy
	'reviewedpages-none' => 'لا توجد صفحات فى هذه القائمة',
	'reviewedpages-lev-0' => 'منظورة',
	'reviewedpages-lev-1' => 'جودة',
	'reviewedpages-lev-2' => 'مختارة',
	'reviewedpages-all' => 'نسخ مراجعة',
	'reviewedpages-best' => 'مراجعه فائقة', # Fuzzy
);

/** Asturian (asturianu)
 * @author Esbardu
 * @author Xuacu
 */
$messages['ast'] = array(
	'reviewedpages' => 'Páxines revisaes',
	'reviewedpages-leg' => 'Llista de les páxines que tan revisaes',
	'reviewedpages-list' => "Esta llista contién páxines [[{{MediaWiki:Validationpage}}|revisaes]] pa les qu'el nivel de revisión ''más altu algamáu'' (pa una revisión) ye'l nivel especificáu.
	Se da un enllaz a la cabera revisión d'esi nivel.",
	'reviewedpages-none' => 'Nun hai páxines nesta llista.',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'versiones revisaes',
	'reviewedpages-best' => 'meyor revisión',
);

/** Azerbaijani (azərbaycanca)
 * @author Vugar 1981
 */
$messages['az'] = array(
	'reviewedpages' => 'Səhifələrin yoxlanması',
	'reviewedpages-leg' => 'Yoxlanılmış səhifələri sadala',
	'reviewedpages-list' => "Bu siyahı, (bir yoxlamanın) ''ən yüksək qazanılmış'' yoxlama səviyyəsi,müəyyən edilmiş səviyyə olan [[{{MediaWiki:Validationpage}}|yoxlanmış]] səhifələri ehtiva edir.
O seviyedeki en son revizyona bağlantı verilmiştir.",
	'reviewedpages-none' => 'Bu siyahıda səhifə yoxdur.',
	'reviewedpages-all' => 'Yoxlanılmış versiyalar',
	'reviewedpages-best' => 'ən yaxşı versiya',
);

/** Southern Balochi (بلوچی مکرانی)
 * @author Mostafadaneshvar
 */
$messages['bcc'] = array(
	'reviewedpages' => 'صفحات بازبینی بوتگین',
	'reviewedpages-leg' => 'لیست صفحات بازبینی بوتگین په یک سطح حاص', # Fuzzy
	'reviewedpages-list' => 'جهلیگین صفحات به مشخصین سطح بازبینی بوتت.', # Fuzzy
	'reviewedpages-none' => 'ته ای لیست هچ صفحه ای نیست',
	'reviewedpages-lev-0' => 'رویت بیتت',
	'reviewedpages-lev-1' => 'کیفیت',
	'reviewedpages-lev-2' => 'نمایان',
	'reviewedpages-all' => 'نسخه یان بازبینی بوتگین',
	'reviewedpages-best' => 'بازبینی اصلی', # Fuzzy
);

/** Belarusian (беларуская)
 * @author Хомелка
 */
$messages['be'] = array(
	'reviewedpages' => 'Правераныя старонкі',
	'reviewedpages-leg' => 'Пералік старонак, якія былі дагледжаны',
	'reviewedpages-list' => "На гэтай старонцы пералічаны [[{{MediaWiki:Validationpage}}|правераныя]] старонкі, чыя ''найлепшая адзнака'' (адной з версій) адпавядае паказанаму узроўню.
Спасылка вядзе на апошнюю версію старонкі з гэтай адзнакай.",
	'reviewedpages-none' => 'Няма старонак у гэтым пераліку.',
	'reviewedpages-all' => 'Правераныя версіі',
	'reviewedpages-best' => 'найлепшая версія',
);

/** Belarusian (Taraškievica orthography) (беларуская (тарашкевіца)‎)
 * @author EugeneZelenko
 * @author Jim-by
 */
$messages['be-tarask'] = array(
	'reviewedpages' => 'Правераныя старонкі',
	'reviewedpages-leg' => 'Сьпіс прарэцэнзаваных старонак',
	'reviewedpages-list' => "Гэты сьпіс утрымлівае [[{{MediaWiki:Validationpage}}|рэцэнзаваныя]] старонкі, ''найлепшы ўзровень'' якіх (адной з вэрсіяў) адпавядае пазначанаму ўзроўню.
Спасылка пададзеная на апошнюю вэрсію з гэтым узроўнем.",
	'reviewedpages-none' => 'У гэтым сьпісе няма старонак',
	'reviewedpages-all' => 'правераныя вэрсіі',
	'reviewedpages-best' => 'найлепшая вэрсія',
);

/** Bulgarian (български)
 * @author Borislav
 * @author DCLXVI
 */
$messages['bg'] = array(
	'reviewedpages' => 'Рецензирани страници',
	'reviewedpages-leg' => 'Преглед на страниците, получили определена оценка', # Fuzzy
	'reviewedpages-list' => 'Следните страници са получили оценка до определената', # Fuzzy
	'reviewedpages-none' => 'Списъкът не съдържа страници',
	'reviewedpages-lev-0' => 'Прегледани',
	'reviewedpages-lev-1' => 'Качество',
	'reviewedpages-lev-2' => 'Избрани',
	'reviewedpages-all' => 'рецензирани версии',
	'reviewedpages-best' => 'последната най-високо оценена версия', # Fuzzy
);

/** Bengali (বাংলা)
 * @author Bellayet
 */
$messages['bn'] = array(
	'reviewedpages' => 'সংশোধিত পাতাসমূহ',
	'reviewedpages-none' => 'তালিকায় কোন পাতা নাই',
	'reviewedpages-all' => 'সংশোধিত সংস্করণসমূহ',
);

/** Breton (brezhoneg)
 * @author Fulup
 * @author Y-M D
 */
$messages['br'] = array(
	'reviewedpages' => 'Pajennoù adwelet',
	'reviewedpages-leg' => 'Rollañ ar pajennoù bet adlennet',
	'reviewedpages-list' => "Rollet eo war ar bajenn-mañ ar pajennoù bet adwelet (d'ar muiañ) d'al live spisaet.

Er roll-mañ ez eus pajennoù [[{{MediaWiki:Validationpage}}|adlennet]] enno unan anezho hag en deus tizhet al live ''uhelañ'' a adlenn resisaet.
Al liamm kinniget a ya war-zu ar stumm ziwezhañ eus al live-se.",
	'reviewedpages-none' => "N'eus pajenn ebet er roll",
	'reviewedpages-lev-1' => 'Perzhded',
	'reviewedpages-lev-2' => 'Lakaet war wel',
	'reviewedpages-all' => 'stummoù adwelet',
	'reviewedpages-best' => 'adweladenn wellañ',
);

/** Bosnian (bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'reviewedpages' => 'Provjerene stranice',
	'reviewedpages-leg' => 'Spisak stranica koje su pregledane',
	'reviewedpages-list' => "Ovaj spisak sadrži [[{{MediaWiki:Validationpage}}|pregledane]] stranice čiji ''najveći dostignuti'' nivo pregleda (revizije) je ujedno traženi nivo. Dati link na posljednju reviziju tog nivoa.",
	'reviewedpages-none' => 'Nema stranica na ovom spisku',
	'reviewedpages-all' => 'pregledane verzije',
	'reviewedpages-best' => 'najbolja revizija',
);

/** Catalan (català)
 * @author Grondin
 * @author Jordi Roqué
 * @author Paucabot
 */
$messages['ca'] = array(
	'reviewedpages' => 'Pàgines revisades',
	'reviewedpages-leg' => "Llista les pàgines que s'ha comprovat",
	'reviewedpages-list' => "Aquesta llista conté pàgines [[{{MediaWiki:Validationpage}}|revistes]] on una dels revisions ha assolit el nivell lectura més alt especificat.
S'ha donat un enllaç cap a l'última versió d'aquell nivell.",
	'reviewedpages-none' => 'No hi ha pàgines a la llista',
	'reviewedpages-all' => 'versions verificades',
	'reviewedpages-best' => 'millor revisió',
);

/** Chechen (нохчийн)
 * @author Sasan700
 */
$messages['ce'] = array(
	'reviewedpages' => 'Хьойсина агlонаш',
	'reviewedpages-all' => 'хьаьжина варсийш',
	'reviewedpages-best' => 'чlогlа дика варси',
);

/** Czech (česky)
 * @author Jezevec
 * @author Li-sung
 * @author Matěj Grabovský
 */
$messages['cs'] = array(
	'reviewedpages' => 'Posouzené stránky',
	'reviewedpages-leg' => 'Seznam stránek podle nejvyšší úrovně posouzení', # Fuzzy
	'reviewedpages-list' => 'Tato stránka obsahuje seznam článků, které byly posouzeny (nejvíce) na určitou úroveň.', # Fuzzy
	'reviewedpages-none' => 'Žádná stránka neodpovídá',
	'reviewedpages-lev-0' => 'prohlédnuté',
	'reviewedpages-lev-1' => 'kvalitní',
	'reviewedpages-lev-2' => 'význačné',
	'reviewedpages-all' => 'posouzené verze',
	'reviewedpages-best' => 'nejlepší verze',
);

/** German (Deutsch)
 * @author Giftpflanze
 * @author Imre
 * @author Kghbln
 * @author Umherirrender
 */
$messages['de'] = array(
	'reviewedpages' => 'Liste der markierten Seiten',
	'reviewedpages-leg' => 'Markierte Seiten auflisten',
	'reviewedpages-list' => 'Diese Listen enthalten [[{{MediaWiki:Validationpage}}|markierte]] Seiten, deren höchster Qualitätsstatus (einer Version) dem angegebenen Status entspricht.
Der Link zu neuesten Version dieses Status ist angegeben.',
	'reviewedpages-none' => 'Diese Liste enthält keine Seiten.',
	'reviewedpages-lev-0' => 'Gesichtet',
	'reviewedpages-lev-1' => 'Geprüft',
	'reviewedpages-lev-2' => 'Exzellent',
	'reviewedpages-all' => 'markierte Versionen',
	'reviewedpages-best' => 'letzte am höchsten bewertete Version',
);

/** Zazaki (Zazaki)
 * @author Erdemaslancan
 * @author Mirzali
 * @author Xoser
 */
$messages['diq'] = array(
	'reviewedpages' => 'Pelanê ke kontrol biyo',
	'reviewedpages-leg' => 'Pelanê ke kontrol biye inan lista bike',
	'reviewedpages-list' => 'Ena liste de [[{{MediaWiki:Validationpage}}|pelanê kontrol biyo]] ke "standarde tewr berzi" (versiyonan ra)  înan ra seviye beliyo.
Versiyone peniyane ena seviye re yew link dayeya.',
	'reviewedpages-none' => 'Ena liste de pelan çini yo',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'versiyonan ke kontrol biyo',
	'reviewedpages-best' => 'revizyono tewr rınd',
);

/** Lower Sorbian (dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'reviewedpages' => 'Pśeglědane boki',
	'reviewedpages-leg' => 'Pśeglědane boki nalicyś',
	'reviewedpages-list' => "Toś ta lisćina wopśimujo [[{{MediaWiki:Validationpage}}|pśeglědane]] boki, kótarychž ''nejwuša dojśpjona'' pśeglědowańska rownina (wersije) jo pódana rownina.
Wótkaz k nejnowšej wersiji teje rowniny jo pódany.",
	'reviewedpages-none' => 'Toś ta lisćina njewopśimujo boki.',
	'reviewedpages-lev-0' => 'Pśeglědany',
	'reviewedpages-lev-1' => 'Kwalita',
	'reviewedpages-lev-2' => 'Wuběrny',
	'reviewedpages-all' => 'pśeglědane wersije',
	'reviewedpages-best' => 'nejlěpša wersija',
);

/** Greek (Ελληνικά)
 * @author Badseed
 * @author Crazymadlover
 * @author Glavkos
 * @author Omnipaedista
 * @author ZaDiak
 */
$messages['el'] = array(
	'reviewedpages' => 'Επιθεωρημένες σελίδες',
	'reviewedpages-leg' => 'Λίστα σελίδων που έχουν εξεταστεί',
	'reviewedpages-list' => "Αυτό λίστες περιέχει [[{{MediaWiki:Validationpage}}|επιθεωρημένες]]  σελίδες, των οποίων το '' υψηλότερα επιτευχθέν '' επίπεδο επιθεώρησης είναι το καθορισμένο επίπεδο. Για την τελευταία αναθεώρηση αυτού του επιπέδου δίνεται μια σύνδεση.",
	'reviewedpages-none' => 'Δεν υπάρχουν σελίδες σε αυτήν τη λίστα',
	'reviewedpages-lev-1' => 'Ποιότητα',
	'reviewedpages-all' => 'αναθεωρημένες εκδόσεις',
	'reviewedpages-best' => 'καλύτερη αναθεώρηση',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'reviewedpages' => 'Kontrolitaj paĝoj',
	'reviewedpages-leg' => 'Listigi paĝojn kontrolitajn',
	'reviewedpages-list' => "Ĉi tiu listigas [[{{MediaWiki:Validationpage}}|kontrolitajn]] paĝojn kies ''plej alta'' kontrolnivelo (de revizio) estas la specifigita nivelo.
Ligilo estas farata al la lasta revizio de tiu nivelo.",
	'reviewedpages-none' => 'Neniuj paĝoj estas en ĉi tiu listo',
	'reviewedpages-lev-0' => 'Reviziita',
	'reviewedpages-lev-1' => 'Kvalito',
	'reviewedpages-lev-2' => 'Elstara',
	'reviewedpages-all' => 'reviziitaj versioj',
	'reviewedpages-best' => 'plej bona revizio',
);

/** Spanish (español)
 * @author Crazymadlover
 * @author Imre
 */
$messages['es'] = array(
	'reviewedpages' => 'Páginas revisadas',
	'reviewedpages-leg' => 'Listar páginas que han sido revisadas',
	'reviewedpages-list' => "Esta lista contiene páginas [[{{MediaWiki:Validationpage}}|revisadas]] cuyo nivel de revisión''más altamente alcanzado''  (de una revisión) es el nivel especificado.
Se da un enlace a la última revisión de este nivel.",
	'reviewedpages-none' => 'No hay páginas en esta lista',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'versiones revisadas',
	'reviewedpages-best' => 'Mejor revisión',
);

/** Estonian (eesti)
 * @author Avjoska
 * @author Pikne
 */
$messages['et'] = array(
	'reviewedpages' => 'Ülevaadatud leheküljed',
	'reviewedpages-leg' => 'Ülevaadatud lehekülgede loetlemine',
	'reviewedpages-list' => "See loend sisaldab [[{{MediaWiki:Validationpage}}|ülevaadatud]] lehekülgi, mille ''kõrgeimaks saavutatud'' (redaktsiooni) ülevaatamistasemeks on kindlaks määratud tase.
	Toodud on link viimasele sellel tasemel redaktsioonile.",
	'reviewedpages-none' => 'Siin loendis ei ole lehekülgi',
	'reviewedpages-all' => 'ülevaadatud versioonid',
	'reviewedpages-best' => 'parim redaktsioon',
);

/** Basque (euskara)
 * @author Kobazulo
 */
$messages['eu'] = array(
	'reviewedpages-lev-1' => 'Kalitatea',
);

/** Persian (فارسی)
 * @author Ebraminio
 * @author Huji
 * @author Sahim
 * @author Wayiran
 */
$messages['fa'] = array(
	'reviewedpages' => 'صفحه‌های بررسی شده',
	'reviewedpages-leg' => 'لیست صفحه‌های بازبینی شده',
	'reviewedpages-list' => "این فهرست‌ها دربردارندهٔ صفحه‌های [[{{MediaWiki:Validationpage}}|بازبینی‌شده‌ای]] است که '' بالاترین'' سطح بازبینی بدست‌آمده‌شان (در یک نسخه)، یک سطح مشخص است.
پیوندی به آخرین نسخهٔ آن سطح داده شده است.",
	'reviewedpages-none' => 'صفحه‌ای در این فهرست نیست',
	'reviewedpages-lev-0' => 'بررسی شده',
	'reviewedpages-lev-1' => 'با کیفیت',
	'reviewedpages-lev-2' => 'برگزیده',
	'reviewedpages-all' => 'نسخه‌های بررسی شده',
	'reviewedpages-best' => 'بهترین نسخه',
);

/** Finnish (suomi)
 * @author Cimon Avaro
 * @author Crt
 * @author Olli
 * @author Pxos
 * @author ZeiP
 */
$messages['fi'] = array(
	'reviewedpages' => 'Arvioidut sivut',
	'reviewedpages-leg' => 'Luettele sivut, jotka on arvioitu',
	'reviewedpages-list' => "Tässä luetellaan sivut, joiden versio on [[{{MediaWiki:Validationpage}}|arvioitu]] ''parhaalle laatutasolle'' valitulla tasolla.
Linkki viittaa tämän tason viimeisimpään versioon.",
	'reviewedpages-none' => 'Tässä luettelossa ei ole yhtään sivua.',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'arvioidut versiot',
	'reviewedpages-best' => 'paras versio',
);

/** French (français)
 * @author Dereckson
 * @author Grondin
 * @author IAlex
 * @author Peter17
 * @author PieRRoMaN
 * @author Urhixidur
 */
$messages['fr'] = array(
	'reviewedpages' => 'Pages passées en revue',
	'reviewedpages-leg' => 'Lister les pages qui ont été relues',
	'reviewedpages-list' => "Cette liste contient les pages [[{{MediaWiki:Validationpage}}|relues]] dont une des versions a atteint ''le plus haut'' niveau de relecture spécifié.
Le lien donné pointe vers la dernière version de ce niveau.",
	'reviewedpages-none' => 'Il n’y a aucune page dans cette liste.',
	'reviewedpages-lev-0' => 'Visualisée',
	'reviewedpages-lev-1' => 'De qualité',
	'reviewedpages-lev-2' => 'Distinguée',
	'reviewedpages-all' => 'versions passées en revue',
	'reviewedpages-best' => 'meilleure version',
);

/** Franco-Provençal (arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'reviewedpages' => 'Pâges passâs en revua',
	'reviewedpages-leg' => 'Listar les pâges qu’ont étâ revues',
	'reviewedpages-list' => "Ceta lista contint les pâges [[{{MediaWiki:Validationpage}}|revues]] que yona de les vèrsions at avengiê lo ''ples hôt'' nivél de rèvision spècefiâ.
Lo lim balyê pouente vers la dèrriére vèrsion de cél nivél.",
	'reviewedpages-none' => 'Ceta lista est voueda.',
	'reviewedpages-lev-0' => 'Revua',
	'reviewedpages-lev-1' => 'De qualitât',
	'reviewedpages-lev-2' => 'Sen tache',
	'reviewedpages-all' => 'vèrsions revues',
	'reviewedpages-best' => 'vèrsion la ples bôna',
);

/** Galician (galego)
 * @author Alma
 * @author Toliño
 */
$messages['gl'] = array(
	'reviewedpages' => 'Páxinas revisadas',
	'reviewedpages-leg' => 'Listar as páxinas que foron revisadas',
	'reviewedpages-list' => "Estas listas conteñen as páxinas [[{{MediaWiki:Validationpage}}|revisadas]] cuxo nivel de revisión ''máis alto'' (dunha revisión) é o nivel especificado.
Dáse unha ligazón cara á última revisión dese nivel.",
	'reviewedpages-none' => 'Non hai páxinas nesta lista',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'versións revisadas',
	'reviewedpages-best' => 'mellor revisión',
);

/** Ancient Greek (Ἀρχαία ἑλληνικὴ)
 * @author Omnipaedista
 */
$messages['grc'] = array(
	'reviewedpages' => 'Ἐπιθεωρημέναι δέλτοι',
	'reviewedpages-lev-0' => 'θεωρημένη',
	'reviewedpages-lev-1' => 'ποιοτικὴ ἔκδοσις',
	'reviewedpages-lev-2' => 'Ἐξαίρετος',
	'reviewedpages-all' => 'ἀναθεωρημέναι ἐκδόσεις',
	'reviewedpages-best' => 'βελτίστη ἀναθεώρησις',
);

/** Swiss German (Alemannisch)
 * @author Als-Chlämens
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'reviewedpages' => 'Lischt vu dr Syte, wu vum Fäldhieter gsäh sin',
	'reviewedpages-leg' => 'Priefti Syte uflischte',
	'reviewedpages-list' => "Die Lischte zeigt [[{{MediaWiki:Validationpage}}|priefti]] Syte, wu uf em ''hegschte Nivo'' prieft sin (vun ere Version).

E Link isch aagee zue dr letschte Version uf däm Nivo.",
	'reviewedpages-none' => 'Die Lischt isch läär.',
	'reviewedpages-lev-0' => 'Vum Fäldhieter gsäh',
	'reviewedpages-lev-1' => 'Prieft',
	'reviewedpages-lev-2' => 'Bsundersch glunge',
	'reviewedpages-all' => 'Vum Fäldhieter aagluegti Versione',
	'reviewedpages-best' => 'am hegschte gwärteti Version',
);

/** Hebrew (עברית)
 * @author Amire80
 * @author DoviJ
 * @author Rotemliss
 * @author YaronSh
 */
$messages['he'] = array(
	'reviewedpages' => 'דפים שנסקרו',
	'reviewedpages-leg' => 'רשימת הדפים שנסקרו',
	'reviewedpages-list' => "דף זה מציג את כל הדפים [[{{MediaWiki:Validationpage}}|שנסקרו]] ושרמת הסקירה '''הגבוהה ביותר''' (לגרסה) היא הרמה שצוינה.
מוצג גם קישור לגרסה האחרונה ברמה זו.",
	'reviewedpages-none' => 'אין דפים ברשימה זו',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'גרסאות שנסקרו',
	'reviewedpages-best' => 'הגרסה הטובה ביותר',
);

/** Hindi (हिन्दी)
 * @author Kaustubh
 */
$messages['hi'] = array(
	'reviewedpages' => 'परिक्षण हुए पन्ने',
	'reviewedpages-leg' => 'विशिष्ट गुण मिले पन्ने', # Fuzzy
	'reviewedpages-list' => 'निम्नलिखित पन्ने विशिष्ट गुण प्राप्त हैं', # Fuzzy
	'reviewedpages-none' => 'इस सूची में पन्ने नहीं हैं',
	'reviewedpages-lev-0' => 'चुना हुआ',
	'reviewedpages-lev-1' => 'गुणवत्ता',
	'reviewedpages-lev-2' => 'विशेष',
	'reviewedpages-all' => 'परिक्षण हुए अवतरण',
	'reviewedpages-best' => 'मूल अवतरण', # Fuzzy
);

/** Croatian (hrvatski)
 * @author Dnik
 * @author Ex13
 * @author SpeedyGonsales
 */
$messages['hr'] = array(
	'reviewedpages' => 'Ocijenjene stranice',
	'reviewedpages-leg' => 'Prikaži ocijenjene stranice',
	'reviewedpages-list' => "Ovaj popis sadrži [[{{MediaWiki:Validationpage}}|ocijenjene]] stranice čija je ''najviša dostignuta'' ocjena jednaka zadanoj.
Navedena je poveznica do najnovije inačice stranice koja je ocijenjena najmanje zadanom ocjenom.",
	'reviewedpages-none' => 'Nema stranica u ovom popisu',
	'reviewedpages-lev-0' => 'Pregledani članci',
	'reviewedpages-lev-1' => 'Kvalitetni članci',
	'reviewedpages-lev-2' => 'Izvrsni članci',
	'reviewedpages-all' => 'ocjenjene verzije',
	'reviewedpages-best' => 'najbolje ocijenjena inačica',
);

/** Upper Sorbian (hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'reviewedpages' => 'Pruwowane strony',
	'reviewedpages-leg' => 'Přepruwowane strony nalistować',
	'reviewedpages-list' => "Tuta lisćina wobsahuje [[{{MediaWiki:Validationpage}}|přepruwowane]] strony, kotrychž ''najlěpša docpěta'' pruwowanska runina (wersije) je podata runina.
Wotkaz k najnowšej wersiji teje runiny je podaty.",
	'reviewedpages-none' => 'W tutej lisćinje strony njejsu',
	'reviewedpages-lev-0' => 'Přehladany',
	'reviewedpages-lev-1' => 'Kwalita',
	'reviewedpages-lev-2' => 'Z funkcijemi',
	'reviewedpages-all' => 'přepruwowane wersije',
	'reviewedpages-best' => 'najlěpša wersija',
);

/** Hungarian (magyar)
 * @author Dani
 * @author Glanthor Reviol
 * @author KossuthRad
 * @author Samat
 */
$messages['hu'] = array(
	'reviewedpages' => 'Ellenőrzött lapok',
	'reviewedpages-leg' => 'Ellenőrzött lapok listázása',
	'reviewedpages-list' => "A lista azokat az [[{{MediaWiki:Validationpage}}|ellenőrzött]] lapokat tartalmazza, amelyek ''legmagasabb'' ellenőrzési szintje egy változatnál a megadott szint.

A hivatkozások az illető értékelési szint legutolsó változatára mutatnak.",
	'reviewedpages-none' => 'Nem található egyetlen lap sem a listában.',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'ellenőrzött változatok',
	'reviewedpages-best' => 'legjobb változat',
);

/** Interlingua (interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'reviewedpages' => 'Paginas revidite',
	'reviewedpages-leg' => 'Listar paginas que ha essite revidite',
	'reviewedpages-list' => "Iste pagina contine paginas [[{{MediaWiki:Validationpage}}|revidite]] del quales le nivello de revision ''le plus alte attingite'' (de un version) es le nivello specificate. Un ligamine es date al version le plus recente de iste nivello.",
	'reviewedpages-none' => 'Il non ha paginas in iste lista',
	'reviewedpages-lev-0' => 'Mirate',
	'reviewedpages-lev-1' => 'Qualitate',
	'reviewedpages-lev-2' => 'Pristine',
	'reviewedpages-all' => 'versiones revidite',
	'reviewedpages-best' => 'le melior version',
);

/** Indonesian (Bahasa Indonesia)
 * @author Bennylin
 * @author IvanLanin
 * @author Iwan Novirion
 * @author Rex
 */
$messages['id'] = array(
	'reviewedpages' => 'Halaman tertinjau',
	'reviewedpages-leg' => 'Daftar halaman yang telah ditinjau',
	'reviewedpages-list' => "Daftar ini berisi [[{{MediaWiki:Validationpage}}|peninjauan]] halaman yang telah ''mencapai tingkat tertinggi'' level peninjauan (dari perbaikan) pada level tertentu.
Sebuah pranala diberikan pada perbaikan terakhir level tersebut.",
	'reviewedpages-none' => 'Tidak ada halaman di dalam daftar ini',
	'reviewedpages-lev-0' => 'Terperiksa',
	'reviewedpages-lev-1' => 'Layak',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'versi tertinjau',
	'reviewedpages-best' => 'Perbaikan terbaik',
);

/** Icelandic (íslenska)
 * @author S.Örvarr.S
 * @author Snævar
 * @author Spacebirdy
 */
$messages['is'] = array(
	'reviewedpages' => 'Yfirfarnar síður',
	'reviewedpages-leg' => 'Listi yfir síður sem hafa verið yfirfarnar',
	'reviewedpages-none' => 'Engar útgáfur eru í þessum lista.',
	'reviewedpages-lev-0' => 'Yfirfarið',
	'reviewedpages-lev-1' => 'Gæði',
	'reviewedpages-all' => 'yfirfarnar útgáfur',
	'reviewedpages-best' => 'besta útgáfan',
);

/** Italian (italiano)
 * @author Beta16
 * @author Darth Kule
 * @author Gianfranco
 * @author Pietrodn
 */
$messages['it'] = array(
	'reviewedpages' => 'Pagine revisionate',
	'reviewedpages-leg' => 'Elenca le pagine revisionate',
	'reviewedpages-list' => "Questo elenco contiene le [[{{MediaWiki:Validationpage}}|pagine controllate]] il cui massimo livello di revisione raggiunto (di una revisione) è il livello specificato.
E' fornito un link all'ultima revisione per quel livello.",
	'reviewedpages-none' => 'Non ci sono pagine in questo elenco',
	'reviewedpages-lev-0' => 'Visionata',
	'reviewedpages-lev-1' => 'Qualità',
	'reviewedpages-lev-2' => 'Ottima',
	'reviewedpages-all' => 'versioni revisionate',
	'reviewedpages-best' => 'versione migliore',
);

/** Japanese (日本語)
 * @author Aotake
 * @author Fryed-peach
 * @author Hosiryuhosi
 * @author JtFuruhata
 * @author Schu
 * @author Shirayuki
 */
$messages['ja'] = array(
	'reviewedpages' => '査読済ページ',
	'reviewedpages-leg' => '査読済みのページを一覧',
	'reviewedpages-list' => 'このページは[[{{MediaWiki:Validationpage}}|査読]]結果の最高値が指定した水準にあるページを一覧しています。水準に達している中で最新の版にリンクしています。',
	'reviewedpages-none' => '表示すべきページはありません',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => '全査読済版',
	'reviewedpages-best' => '最良版',
);

/** Jutish (jysk)
 * @author Ælsån
 */
$messages['jut'] = array(
	'reviewedpages' => 'Sæn pæger',
	'reviewedpages-leg' => 'Liste pæger sæn til æ ståndvast nivå', # Fuzzy
	'reviewedpages-list' => 'Æ følgende pæger er sæn til æ spæsifiærn nivå', # Fuzzy
	'reviewedpages-none' => 'Her er ekke pæger in dette liste',
	'reviewedpages-lev-0' => 'Sæn',
	'reviewedpages-lev-1' => 'Kwalititæt',
	'reviewedpages-lev-2' => 'Gådvårende',
	'reviewedpages-all' => 'sæn versje',
	'reviewedpages-best' => 'erste versje', # Fuzzy
);

/** Georgian (ქართული)
 * @author BRUTE
 * @author David1010
 * @author ITshnik
 */
$messages['ka'] = array(
	'reviewedpages' => 'შემოწმებული გვერდები',
	'reviewedpages-leg' => 'შემოწმებული გვერდების სია',
	'reviewedpages-none' => 'ამ სიაში გვერდები არ არის',
	'reviewedpages-all' => 'შემოწმებული ვერსიები',
	'reviewedpages-best' => 'საუკეთესო ვერსია',
);

/** Kazakh (Arabic script) (قازاقشا (تٴوتە)‏)
 */
$messages['kk-arab'] = array(
	'reviewedpages' => 'سىن بەرىلگەن بەتتەر',
	'reviewedpages-leg' => 'انىق دەڭگەيدە سىن بەرىلگەن بەتتەردى تىزىمدەۋ', # Fuzzy
	'reviewedpages-list' => 'كەلەسى بەتتەرگە كەلتىرىلگەن دەڭگەيدە سىن بەرىلگەن', # Fuzzy
	'reviewedpages-none' => 'بۇل تىزىمدە ەش بەت جوق',
	'reviewedpages-lev-0' => 'شولىنعان',
	'reviewedpages-lev-1' => 'ساپالى',
	'reviewedpages-lev-2' => 'تاڭدامالى',
	'reviewedpages-all' => 'سىن بەرىلگەن نۇسقالارى',
	'reviewedpages-best' => 'ەڭ سوڭعى ەڭ جوعارى دەڭگەي بەرىلگەن نۇسقاسى', # Fuzzy
);

/** Kazakh (Cyrillic script) (қазақша (кирил)‎)
 */
$messages['kk-cyrl'] = array(
	'reviewedpages' => 'Сын берілген беттер',
	'reviewedpages-leg' => 'Анық деңгейде сын берілген беттерді тізімдеу', # Fuzzy
	'reviewedpages-list' => 'Келесі беттерге келтірілген деңгейде сын берілген', # Fuzzy
	'reviewedpages-none' => 'Бұл тізімде еш бет жоқ',
	'reviewedpages-lev-0' => 'шолынған',
	'reviewedpages-lev-1' => 'сапалы',
	'reviewedpages-lev-2' => 'таңдамалы',
	'reviewedpages-all' => 'сын берілген нұсқалары',
	'reviewedpages-best' => 'ең соңғы ең жоғары деңгей берілген нұсқасы', # Fuzzy
);

/** Kazakh (Latin script) (qazaqşa (latın)‎)
 */
$messages['kk-latn'] = array(
	'reviewedpages' => 'Sın berilgen better',
	'reviewedpages-leg' => 'Anıq deñgeýde sın berilgen betterdi tizimdew', # Fuzzy
	'reviewedpages-list' => 'Kelesi betterge keltirilgen deñgeýde sın berilgen', # Fuzzy
	'reviewedpages-none' => 'Bul tizimde eş bet joq',
	'reviewedpages-lev-0' => 'şolınğan',
	'reviewedpages-lev-1' => 'sapalı',
	'reviewedpages-lev-2' => 'tañdamalı',
	'reviewedpages-all' => 'sın berilgen nusqaları',
	'reviewedpages-best' => 'eñ soñğı eñ joğarı deñgeý berilgen nusqası', # Fuzzy
);

/** Khmer (ភាសាខ្មែរ)
 * @author Chhorran
 * @author គីមស៊្រុន
 */
$messages['km'] = array(
	'reviewedpages-none' => 'មិនមានទំព័រណាមួយក្នុងបញ្ជីនេះទេ',
	'reviewedpages-lev-1' => 'គុណភាព',
);

/** Korean (한국어)
 * @author Devunt
 * @author Kwj2772
 */
$messages['ko'] = array(
	'reviewedpages' => '검토된 문서',
	'reviewedpages-leg' => '검토된 문서들의 목록',
	'reviewedpages-list' => "이 목록은 특정 판에 대하여 등급이 가장 ''높은 수준''으로 평가된 것이 주어진 등급에 해당하는 [[{{MediaWiki:Validationpage}}|검토]]된 문서를 포함하고 있습니다.
해당 수준에서 가장 최근의 편집에 대한 링크가 주어져 있습니다.",
	'reviewedpages-none' => '이 목록에 문서가 없습니다.',
	'reviewedpages-all' => '검토된 버전',
	'reviewedpages-best' => '최고 판',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'reviewedpages' => 'Nohjekik Versione',
	'reviewedpages-leg' => 'Nohjekik Sigge opleßte',
	'reviewedpages-list' => 'Hee di {{PLURAL:$1|Sigg es|Sigge sen|noll Sigge sen}} [[{{MediaWiki:Validationpage}}|nohjekik]].
Et hühste Nivoh vun öhns ene Version vun dä {{PLURAL:$1|Sigg sullt dat|Sigge sullt dat|noll Sigge sulld et}} aanjejovve Nivoh sin. Dä Lengk en jeedem Leßte-Endraach jeihd immer op de neuste Version met dämm Nivoh.',
	'reviewedpages-none' => 'En dä Leß sin kein Sigge.',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'nohjekik Versione',
	'reviewedpages-best' => 'de beste Version',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'reviewedpages' => 'Lëscht vun den iwwerkuckte Säiten',
	'reviewedpages-leg' => 'Lëscht vu Säite déi nogekuckt goufen',
	'reviewedpages-list' => "Op dëser Säit stinn  [[{{MediaWiki:Validationpage}}|nogekuckte]] Säiten deene hiren ''héichsten erreeechten'' Niveau (vun enger Versioun) deen Niveau ass deen uginn ass.
Et gëtt e Link op déi leschte Versioun vun deem Niveau.",
	'reviewedpages-none' => 'Dës Lëscht ass eidel',
	'reviewedpages-lev-0' => 'iwwerkuckt',
	'reviewedpages-lev-1' => 'Qualitéit',
	'reviewedpages-lev-2' => 'Exzellent',
	'reviewedpages-all' => 'nogekuckte Versiounen',
	'reviewedpages-best' => 'bescht Versioun',
);

/** Limburgish (Limburgs)
 * @author Matthias
 * @author Ooswesthoesbes
 */
$messages['li'] = array(
	'reviewedpages' => "Beoordeilde pagina's",
	'reviewedpages-leg' => "Liest mit gecontroleerde pagina's",
	'reviewedpages-list' => "De volgende pagina's zeen [[{{MediaWiki:Validationpage}}|gecontroleerd]] toet 't '''hoeags aangegaeve''' niveau (van 'n versie).
d'r Weurt 'n verwiezing gegaeve nao de leste versie veur det niveau.",
	'reviewedpages-none' => "Er staan geen pagina's in deze lijst",
	'reviewedpages-lev-0' => 'Beoordeild',
	'reviewedpages-lev-1' => 'Kwaliteit',
	'reviewedpages-lev-2' => 'Oetgeleech',
	'reviewedpages-all' => 'beoordeilde versies',
	'reviewedpages-best' => 'beste versie',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 * @author Brest
 */
$messages['mk'] = array(
	'reviewedpages' => 'Оценети страници',
	'reviewedpages-leg' => 'Список на проверени страници',
	'reviewedpages-list' => "Овие списоци содржат [[{{MediaWiki:Validationpage}}|проверени]] страници чие назначено ниво е ''највисоко'' оценето ниво (на ревизија).
Наведена е врска кон најновата ревизија на тоа ниво.",
	'reviewedpages-none' => 'Нема страници на овој список.',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => 'Квалитетни',
	'reviewedpages-lev-2' => 'Одлични',
	'reviewedpages-all' => 'оценети верзии',
	'reviewedpages-best' => 'најдобра ревизија',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 * @author Shijualex
 */
$messages['ml'] = array(
	'reviewedpages' => 'സംശോധനം നടന്ന താളുകൾ',
	'reviewedpages-leg' => 'സംശോധനം ചെയ്യപ്പെട്ട താളുകളുടെ പട്ടിക',
	'reviewedpages-list' => "വ്യക്തമാക്കപ്പെട്ടിട്ടുള്ള തലത്തിന്റെ ''സാദ്ധ്യമായതിൽ ഉന്നതമായ'' സംശോധന തലത്തിൽ (ഒരു നാൾപ്പതിപ്പിന്റെ) എത്തിച്ചേരാൻ കഴിഞ്ഞ [[{{MediaWiki:Validationpage}}|സംശോധനം ചെയ്ത]] താളുകൾ ഈ പട്ടികയിൽ നൽകിയിരിക്കുന്നു.
ആ തലത്തിലെ ഏറ്റവും പുതിയ നാൾപ്പതിപ്പിലേയ്ക്കുള്ള കണ്ണിയും നൽകിയിരിക്കുന്നു.",
	'reviewedpages-none' => 'ഈ പട്ടികയിൽ താളുകൾ ഒന്നും ഇല്ല',
	'reviewedpages-lev-0' => 'സൈറ്റഡ്',
	'reviewedpages-lev-1' => 'ഉന്നത നിലവാരം',
	'reviewedpages-lev-2' => 'തിരഞ്ഞെടുക്കപ്പെട്ടത്',
	'reviewedpages-all' => 'സംശോധനം ചെയ്ത പതിപ്പുകൾ',
	'reviewedpages-best' => 'ഏറ്റവും നല്ല നാൾപ്പതിപ്പ്',
);

/** Marathi (मराठी)
 * @author Kaustubh
 */
$messages['mr'] = array(
	'reviewedpages' => 'तपासलेली पाने',
	'reviewedpages-leg' => 'विशिष्ट गुणांकनाला तपासलेली पाने', # Fuzzy
	'reviewedpages-list' => 'खालील पाने दिलेल्या विशिष्ट गुणांकनात तपासलेली आहेत', # Fuzzy
	'reviewedpages-none' => 'या यादीत पाने नाहीत',
	'reviewedpages-lev-0' => 'निवडलेली',
	'reviewedpages-lev-1' => 'गुणवत्ता',
	'reviewedpages-lev-2' => 'विशेष',
	'reviewedpages-all' => 'तपासलेल्या आवृत्त्या',
	'reviewedpages-best' => 'मूळ आवृत्ती', # Fuzzy
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 * @author Aviator
 * @author Kurniasan
 */
$messages['ms'] = array(
	'reviewedpages' => 'Laman diperiksa',
	'reviewedpages-leg' => 'Senaraikan halaman yang telah dikaji semula',
	'reviewedpages-list' => "Halaman ini mengandungi laman-laman [[{{MediaWiki:Validationpage}}|dikaji semula]] yang tahap kajian semula (semakan) ''tertinggi yang dicapai'' merupakan tahap yang dinyatakan.
	Satu pautan diberikan kepada semakan terkini pada tahap itu.",
	'reviewedpages-none' => 'Tiada laman dalam senarai ini',
	'reviewedpages-lev-0' => '{{Int: revreview-Lev-asas}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'versi-versi dikaji semula',
	'reviewedpages-best' => 'semakan terbaik',
);

/** Maltese (Malti)
 * @author Chrisportelli
 */
$messages['mt'] = array(
	'reviewedpages' => 'Paġni riveduti',
	'reviewedpages-leg' => 'Elenka l-paġni li ġew riveduti',
	'reviewedpages-none' => "M'hemm l-ebda paġna f'din il-lista.",
	'reviewedpages-all' => 'verżjonijiet riveduti',
	'reviewedpages-best' => 'l-aqwa reviżjoni',
);

/** Nahuatl (Nāhuatl)
 * @author Fluence
 */
$messages['nah'] = array(
	'reviewedpages-lev-1' => 'Cuallōtl',
);

/** Norwegian Bokmål (norsk (bokmål)‎)
 * @author EivindJ
 * @author Nghtwlkr
 */
$messages['nb'] = array(
	'reviewedpages' => 'Anmeldte sider',
	'reviewedpages-leg' => 'List opp sider som har blitt revidert',
	'reviewedpages-list' => "Denne listen inneholder [[{{MediaWiki:Validationpage}}|reviderte]] sider som ''på det meste''  har hatt et revisjonsnivå på et oppgitt nivå.
En lenke er oppgitt til den siste revisjonen med det nivået.",
	'reviewedpages-none' => 'Det er ingen sider i denne listen',
	'reviewedpages-lev-0' => 'Sjekket',
	'reviewedpages-lev-1' => 'Kvalitet',
	'reviewedpages-lev-2' => 'Utmerket',
	'reviewedpages-all' => 'anmeldte sideversjoner',
	'reviewedpages-best' => 'beste revisjon',
);

/** Dutch (Nederlands)
 * @author McDutchie
 * @author SPQRobin
 * @author Siebrand
 */
$messages['nl'] = array(
	'reviewedpages' => "Gecontroleerde pagina's",
	'reviewedpages-leg' => "Lijst met pagina's die gecontroleerd zijn",
	'reviewedpages-list' => "De volgende pagina's zijn [[{{MediaWiki:Validationpage}}|gecontroleerd]] tot het '''hoogst aangegeven''' niveau (van een versie).
Er wordt een koppeling gegeven naar de laatste versie voor dat niveau.",
	'reviewedpages-none' => "Er staan geen pagina's in deze lijst.",
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => 'Kwaliteit',
	'reviewedpages-lev-2' => 'Uitgelicht',
	'reviewedpages-all' => 'gecontroleerde versies',
	'reviewedpages-best' => 'beste versie',
);

/** Norwegian Nynorsk (norsk (nynorsk)‎)
 * @author Harald Khan
 * @author Njardarlogar
 */
$messages['nn'] = array(
	'reviewedpages' => 'Vurderte sider',
	'reviewedpages-leg' => 'List opp sider etter høgaste nivå', # Fuzzy
	'reviewedpages-list' => 'Dei fylgjande sidene har vortne vurderte som det oppgjevne nivået', # Fuzzy
	'reviewedpages-none' => 'Det finst ingen sider i denne lista',
	'reviewedpages-lev-0' => 'Vurdert',
	'reviewedpages-lev-1' => 'Kvalitet',
	'reviewedpages-lev-2' => 'Utmerka',
	'reviewedpages-all' => 'vurderte versjonar',
	'reviewedpages-best' => 'beste versjon', # Fuzzy
);

/** Northern Sotho (Sesotho sa Leboa)
 * @author Mohau
 */
$messages['nso'] = array(
	'reviewedpages-none' => 'Gago matlakala go lenano le',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'reviewedpages' => 'Pagina passadas en revista',
	'reviewedpages-leg' => 'Far la lista de las paginas que son estadas relegidas',
	'reviewedpages-list' => 'Aquesta pagina fa la lista de las paginas en revista (al mai) al nivèl especificat.', # Fuzzy
	'reviewedpages-none' => 'Aquesta lista es voida',
	'reviewedpages-lev-0' => 'Visualizat',
	'reviewedpages-lev-1' => 'De qualitat',
	'reviewedpages-lev-2' => 'Mes en abans',
	'reviewedpages-all' => 'versions passadas en revista',
	'reviewedpages-best' => 'Melhor revision',
);

/** Oriya (ଓଡ଼ିଆ)
 * @author Ansumang
 */
$messages['or'] = array(
	'reviewedpages' => 'ସମୀକ୍ଷା ପୃଷ୍ଠା',
);

/** Polish (polski)
 * @author Leinad
 * @author Sp5uhe
 */
$messages['pl'] = array(
	'reviewedpages' => 'Przejrzane strony',
	'reviewedpages-leg' => 'Lista przejrzanych stron',
	'reviewedpages-list' => "Lista zawiera [[{{MediaWiki:Validationpage}}|przejrzane]] strony, których '''najwyższy poziom''' w przeglądaniu (wersji) jest wybranym poziomem.
Podany link prowadzi do ostatniej wersji na tym poziomie.",
	'reviewedpages-none' => 'Brak stron na tej liście',
	'reviewedpages-lev-0' => 'Przejrzane',
	'reviewedpages-lev-1' => 'Zweryfikowane',
	'reviewedpages-lev-2' => 'Doskonałe',
	'reviewedpages-all' => 'wersje oznaczone',
	'reviewedpages-best' => 'wersja najlepsza',
);

/** Piedmontese (Piemontèis)
 * @author Borichèt
 * @author Dragonòt
 */
$messages['pms'] = array(
	'reviewedpages' => 'Pàgine revisionà',
	'reviewedpages-leg' => "Listé le pàgine ch'a son ëstàite revisionà",
	'reviewedpages-list' => "Sta lista-sì a conten le pàgine [[{{MediaWiki:Validationpage}}|revisionà]] dont ël ''pì àut'' livel ëd revision (ëd na revision) a l'é ël livel spessificà.
Un colegament a l'é dàit a l'ùltima revision ëd col livel.",
	'reviewedpages-none' => 'A-i é pa ëd pàgine an sta lista-sì',
	'reviewedpages-all' => 'vërsion revisionà',
	'reviewedpages-best' => 'la mèj revision',
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'reviewedpages-none' => 'په همدې لړليک کې هېڅ کوم مخ نشته',
);

/** Portuguese (português)
 * @author Giro720
 * @author Hamilton Abreu
 * @author Malafaya
 * @author Waldir
 * @author 555
 */
$messages['pt'] = array(
	'reviewedpages' => 'Páginas revistas',
	'reviewedpages-leg' => 'Listar as páginas revistas',
	'reviewedpages-list' => 'Esta lista contém páginas [[{{MediaWiki:Validationpage}}|revistas]] cuja classificação mais elevada na revisão é o nível especificado.
	Existe um link para a última edição com o mesmo nível.',
	'reviewedpages-none' => 'Não há páginas nesta lista',
	'reviewedpages-lev-0' => 'Objetiva',
	'reviewedpages-lev-1' => 'Qualidade',
	'reviewedpages-lev-2' => 'Exemplar',
	'reviewedpages-all' => 'versões revistas',
	'reviewedpages-best' => 'melhor edição',
);

/** Brazilian Portuguese (português do Brasil)
 * @author Eduardo.mps
 * @author Giro720
 */
$messages['pt-br'] = array(
	'reviewedpages' => 'Páginas analisadas',
	'reviewedpages-leg' => 'Listar as páginas revisadas',
	'reviewedpages-list' => 'Esta lista contém páginas [[{{MediaWiki:Validationpage}}|revisadas]] cuja classificação mais elevada na revisão é o nível especificado.
Existe um link para a última edição com o mesmo nível.',
	'reviewedpages-none' => 'Não há páginas nesta lista',
	'reviewedpages-lev-0' => 'Objetiva',
	'reviewedpages-lev-1' => 'Qualidade',
	'reviewedpages-lev-2' => 'Exemplar',
	'reviewedpages-all' => 'edições analisadas',
	'reviewedpages-best' => 'melhor revisão',
);

/** Romanian (română)
 * @author Firilacroco
 * @author KlaudiuMihaila
 * @author Mihai
 * @author Minisarm
 * @author Stelistcristi
 */
$messages['ro'] = array(
	'reviewedpages' => 'Pagini revizuite',
	'reviewedpages-leg' => 'Afișarea paginilor revizuite',
	'reviewedpages-list' => "Această listă conține paginile [[{{MediaWiki:Validationpage}}|revizuite]] al căror nivel ''maxim'' de revizuire (al unei versiuni) este cel specificat.
O legătură către ultima versiune de acel nivel este furnizată.",
	'reviewedpages-none' => 'Nu există pagini în această listă',
	'reviewedpages-lev-1' => 'Calitate',
	'reviewedpages-all' => 'versiuni revizuite',
	'reviewedpages-best' => 'cea mai bună versiune',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'reviewedpages' => 'Pàggene reviste',
	'reviewedpages-leg' => 'Elenghe de le pàggene ca onne state reviste',
	'reviewedpages-list' => "Ste elenghe condène le pàggene [[{{MediaWiki:Validationpage}}|reviste]] cu 'u ''cchiù ierte '' grade de attendibbeletà (de 'na revisione) jè 'u levèlle specificate.<br />
'Nu collegamende jè date pe l'urtema revisione de quidde levèlle.",
	'reviewedpages-none' => "Non ge stonne pàggene jndr'à sta liste",
	'reviewedpages-lev-0' => 'Viste',
	'reviewedpages-lev-1' => 'Qualità',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'versiune reviste',
	'reviewedpages-best' => 'megghia revisione',
);

/** Russian (русский)
 * @author Ferrer
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'reviewedpages' => 'Проверенные страницы',
	'reviewedpages-leg' => 'Список проверенных страниц',
	'reviewedpages-list' => "На этой странице перечислены [[{{MediaWiki:Validationpage}}|проверенные]] страницы, чья ''наилучшая оценка'' (одной из версий) соответствует указанному уровню.
Ссылка ведёт на последнюю версию страницы с этой оценкой.",
	'reviewedpages-none' => 'В данном списке отсутствуют страницы',
	'reviewedpages-lev-0' => 'Досмотренная',
	'reviewedpages-lev-1' => 'Выверенная',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'проверенные версии',
	'reviewedpages-best' => 'наилучшая версия',
);

/** Rusyn (русиньскый)
 * @author Gazeb
 */
$messages['rue'] = array(
	'reviewedpages' => 'Перевірены сторінкы',
	'reviewedpages-leg' => 'Список сторінок, што были рецензованы',
	'reviewedpages-list' => "Тот список обсягує [[{{MediaWiki:Validationpage}}|рецензованы]] сторінкы, чій ''найвысшый досягнутый'' рівень рецензії (або верзії) є зазначеный рівень.
Одказ веде на остатню верзію того рівня.",
	'reviewedpages-none' => 'У тім списку не суть жадны сторінкы.',
	'reviewedpages-all' => 'перевірены верзії',
	'reviewedpages-best' => 'найлїпша верзія',
);

/** Sakha (саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'reviewedpages' => 'Ырытыллыбыт сирэйдэр',
	'reviewedpages-leg' => 'Тургутуллубут сирэйдэр тиһиктэрэ',
	'reviewedpages-list' => 'Бу сирэйгэ этиллибит таһымҥа эппиэттиир барыллаах [[{{MediaWiki:Validationpage}}|тургутуллубут]] сирэйдэр көрдөрүлүннүлэр.
Сигэ ыстатыйа оннук сыаналаах барылыгар тиэрдиэ.',
	'reviewedpages-none' => 'Испииһэк кураанах',
	'reviewedpages-lev-0' => 'Көрүллүбүт',
	'reviewedpages-lev-1' => 'Бэрэбиэркэлэммит',
	'reviewedpages-lev-2' => 'Талыы-талба',
	'reviewedpages-all' => 'ырытыллыбыт торумнар',
	'reviewedpages-best' => 'бастыҥ барыла',
);

/** Sinhala (සිංහල)
 * @author පසිඳු කාවින්ද
 */
$messages['si'] = array(
	'reviewedpages' => 'නිරීක්ෂණය කෙරූ පිටු',
	'reviewedpages-leg' => 'නිරීක්ෂණය කෙරූ පිටු ලැයිස්තුගත කරන්න',
	'reviewedpages-none' => 'මෙම ලයිස්තුවේ පිටු කිසිවක් නොමැත.',
	'reviewedpages-all' => 'නිරීක්ෂණය කෙරූ අනුවාද',
	'reviewedpages-best' => 'හොඳම සංශෝධනය',
);

/** Slovak (slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'reviewedpages' => 'Skontrolované stránky',
	'reviewedpages-leg' => 'Zoznam stránok podľa najvyššej úrovne kontroly', # Fuzzy
	'reviewedpages-list' => 'Táto stránka obsahuje zoznam článkov, ktoré boli skontrolované (najviac) do určenej úrovne.', # Fuzzy
	'reviewedpages-none' => 'V tomto zozname sa nenachádzajú žiadne stránky.',
	'reviewedpages-lev-0' => 'Videná',
	'reviewedpages-lev-1' => 'Kvalita',
	'reviewedpages-lev-2' => 'Odporúčané',
	'reviewedpages-all' => 'skontrolované verzie',
	'reviewedpages-best' => 'primárna revízia', # Fuzzy
);

/** Slovenian (slovenščina)
 * @author Dbc334
 */
$messages['sl'] = array(
	'reviewedpages' => 'Pregledane strani',
	'reviewedpages-leg' => 'Navede strani, ki so bile pregledane',
	'reviewedpages-list' => "Ta seznam vsebuje [[{{MediaWiki:Validationpage}}|pregledane]] strani, ki imajo določeno ''najvišjo doseženo'' stopnjo pregleda (redakcije).
Podana je tudi povezava do najnovejše redakcije te stopnje.",
	'reviewedpages-none' => 'Na tem seznamu ni strani.',
	'reviewedpages-all' => 'pregledane redakcije',
	'reviewedpages-best' => 'najboljša redakcija',
);

/** Albanian (shqip)
 * @author Puntori
 * @author Tëfcí
 */
$messages['sq'] = array(
	'reviewedpages-lev-1' => 'Kualiteti',
	'reviewedpages-all' => 'versione të shqyrtuara',
);

/** Serbian (Cyrillic script) (српски (ћирилица)‎)
 * @author Charmed94
 * @author Millosh
 * @author Rancher
 * @author Sasa Stefanovic
 * @author Жељко Тодоровић
 * @author Михајло Анђелковић
 */
$messages['sr-ec'] = array(
	'reviewedpages' => 'Прегледане странице',
	'reviewedpages-leg' => 'Списак проверених страница',
	'reviewedpages-list' => "Ови спискови садрже [[{{MediaWiki:Validationpage}}|прегледане]] странице чији ''највиши достигнут'' ниво прегледа (ревизије) је одређени ниво.
Веза је дата најновијој ревизији тог нивоа.",
	'reviewedpages-none' => 'Нема страница у овом списку.',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'прегледана издања',
	'reviewedpages-best' => 'најбоља измена',
);

/** Serbian (Latin script) (srpski (latinica)‎)
 * @author Michaello
 * @author Михајло Анђелковић
 */
$messages['sr-el'] = array(
	'reviewedpages' => 'Pregledane stranice',
	'reviewedpages-leg' => 'Svrstaj strane koje su pregledane',
	'reviewedpages-list' => "Ovi spiskovi sadrže [[{{MediaWiki:Validationpage}}|pregledane]] stranice čiji ''najviši dostignut'' nivo pregleda (revizije) je određeni nivo.
Veza je data najnovijoj reviziji tog nivoa.",
	'reviewedpages-none' => 'Nema strana u ovom spisku.',
	'reviewedpages-lev-0' => 'Pregledano',
	'reviewedpages-lev-1' => 'Kvalitet',
	'reviewedpages-lev-2' => 'Izabrani',
	'reviewedpages-all' => 'pregledane verzije',
	'reviewedpages-best' => 'najbolja revizija',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 */
$messages['stq'] = array(
	'reviewedpages' => 'Wröigede Sieden',
	'reviewedpages-leg' => 'Lieste fon do Sieden mäd dän hoochste Level', # Fuzzy
	'reviewedpages-list' => 'Do foulgjende Sieden wuuden wröiged un hääbe dän anroate Stoatus kriegen', # Fuzzy
	'reviewedpages-none' => 'Ju Lieste is loos.',
	'reviewedpages-lev-0' => 'Sieuwed',
	'reviewedpages-lev-1' => 'Qualität',
	'reviewedpages-lev-2' => 'Exzellent',
	'reviewedpages-all' => 'wröigede Versione',
	'reviewedpages-best' => 'lääste ap hoochste wäidierde Version', # Fuzzy
);

/** Sundanese (Basa Sunda)
 * @author Kandar
 */
$messages['su'] = array(
	'reviewedpages-none' => 'Euweuh kaca dina ieu daptar',
	'reviewedpages-lev-2' => 'Petingan',
);

/** Swedish (svenska)
 * @author Boivie
 * @author Dafer45
 * @author Lejonel
 * @author M.M.S.
 * @author McDutchie
 * @author Najami
 * @author Per
 */
$messages['sv'] = array(
	'reviewedpages' => 'Granskade sidor',
	'reviewedpages-leg' => 'Lista sidor som har blivit granskade',
	'reviewedpages-list' => "Denna förteckning innehåller [[{{MediaWiki:Validationpage}}|granskade]] sidor vars ''högsta uppnådda'' granskningsnivå (för en version) är den angivna nivån.
En länk ges till den senaste versionen av denna nivå.",
	'reviewedpages-none' => 'Den här listan innehåller inga sidor',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'granskade versioner',
	'reviewedpages-best' => 'bästa versionen',
);

/** Tamil (தமிழ்)
 * @author Shanmugamp7
 * @author மதனாஹரன்
 */
$messages['ta'] = array(
	'reviewedpages-none' => 'இப்பட்டியலில் எந்தவொரு பக்கமும் இல்லை.',
	'reviewedpages-best' => 'சிறந்த பரிசீலனை',
);

/** Telugu (తెలుగు)
 * @author Chaduvari
 * @author Kiranmayee
 * @author Veeven
 */
$messages['te'] = array(
	'reviewedpages' => 'సమీక్షించిన పేజీలు',
	'reviewedpages-leg' => 'సమీక్షితమై ఉన్న పుటలను చూపించు',
	'reviewedpages-list' => 'ఈ క్రింద పేర్కొన్న {{PLURAL:$1|పేజీ|పేజీలు}} మీరడిగిన స్థాయివరకు ఎక్కువగా సమీక్షించబడినవి', # Fuzzy
	'reviewedpages-none' => 'ఈ జాబితాలో పేజీలు లేవు.',
	'reviewedpages-lev-0' => 'కనబడింది',
	'reviewedpages-lev-1' => 'నాణ్యత',
	'reviewedpages-lev-2' => 'విశేషం',
	'reviewedpages-all' => 'సమీక్షిత కూర్పులు',
	'reviewedpages-best' => 'ఉత్తమ కూర్పు',
);

/** Tajik (Cyrillic script) (тоҷикӣ)
 * @author Ibrahim
 */
$messages['tg-cyrl'] = array(
	'reviewedpages' => 'Саҳифаҳои баррасӣ нашуда',
	'reviewedpages-leg' => 'Намоиши саҳифаҳое ки то ҳад баррасӣ шудаанд', # Fuzzy
	'reviewedpages-list' => 'Саҳифаҳои зерин то ҳад таъйин шуда, мавриди баррасӣ қарор гирифтаанд', # Fuzzy
	'reviewedpages-none' => 'Саҳифаҳое дар ин феҳрист нест',
	'reviewedpages-lev-0' => 'Баррасишуда',
	'reviewedpages-lev-1' => 'Бо кайфият',
	'reviewedpages-lev-2' => 'Баргузида',
	'reviewedpages-all' => 'нусхаҳои баррасӣ шуда',
	'reviewedpages-best' => 'нусхаи беҳтарин', # Fuzzy
);

/** Tajik (Latin script) (tojikī)
 * @author Liangent
 */
$messages['tg-latn'] = array(
	'reviewedpages' => 'Sahifahoi barrasī naşuda',
	'reviewedpages-none' => 'Sahifahoe dar in fehrist nest',
	'reviewedpages-lev-0' => 'Barrasişuda',
	'reviewedpages-lev-1' => 'Bo kajfijat',
	'reviewedpages-lev-2' => 'Barguzida',
	'reviewedpages-all' => 'nusxahoi barrasī şuda',
	'reviewedpages-best' => 'nusxai behtarin', # Fuzzy
);

/** Turkmen (Türkmençe)
 * @author Hanberke
 */
$messages['tk'] = array(
	'reviewedpages' => 'Gözden geçirilen sahypalar',
	'reviewedpages-leg' => 'Sahypalary iň ýokary gözden geçirme derejesine göre sanawla', # Fuzzy
	'reviewedpages-list' => 'Bu sahypa görkezilen derejede (iň ýokary) gözden geçirilen sahypalary sanawlaýar.', # Fuzzy
	'reviewedpages-none' => 'Bu sanawda hiç hili sahypa ýok',
	'reviewedpages-lev-2' => 'Saýlama',
	'reviewedpages-all' => 'gözden geçirilen wersiýalar',
	'reviewedpages-best' => 'iň gowy wersiýa', # Fuzzy
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'reviewedpages' => 'Nasuring mga pahina',
	'reviewedpages-leg' => 'Itala ang mga pahinang nasuri na',
	'reviewedpages-list' => "Naglalaman ang kawing na ito ng [[{{MediaWiki:Validationpage}}|nasuring]]  mga pahina na ang ''pinakamataas na naabot'' na antas ng pagsusuri (ng isang rebisyon) ay ang antas na tinukoy.  
Ibinigay ang isang kawing papunta sa pinahakuling pagbabago ng ganyang antas.",
	'reviewedpages-none' => 'Walang mga pahina sa loob ng talaang ito',
	'reviewedpages-lev-0' => '{{int:revreview-lev-basic}}',
	'reviewedpages-lev-1' => '{{int:revreview-lev-quality}}',
	'reviewedpages-lev-2' => '{{int:revreview-lev-pristine}}',
	'reviewedpages-all' => 'mga bersyong nasuri na',
	'reviewedpages-best' => 'pinakamainam na pagbabago',
);

/** Turkish (Türkçe)
 * @author Erkan Yilmaz
 * @author Joseph
 * @author Vito Genovese
 */
$messages['tr'] = array(
	'reviewedpages' => 'Gözden geçirilmiş sayfalar',
	'reviewedpages-leg' => 'Gözden geçirilen sayfaları listele',
	'reviewedpages-list' => "Bu liste, (bir revizyonunun) ''en yüksek kazanılmış'' gözden geçirme seviyesi, belirtilen seviye olan [[{{MediaWiki:Validationpage}}|gözden geçirilmiş]] sayfaları içerir.
O seviyedeki en son revizyona bağlantı verilmiştir.",
	'reviewedpages-none' => 'Bu listede hiç sayfa yok',
	'reviewedpages-lev-0' => 'Gözlenmiş',
	'reviewedpages-lev-1' => 'Kalite',
	'reviewedpages-lev-2' => 'Özellikli',
	'reviewedpages-all' => 'gözden geçirilmiş sürümler',
	'reviewedpages-best' => 'en iyi revizyon',
);

/** Ukrainian (українська)
 * @author Ahonc
 * @author Prima klasy4na
 */
$messages['uk'] = array(
	'reviewedpages' => 'Перевірені сторінки',
	'reviewedpages-leg' => 'Список сторінок, що були рецензовані',
	'reviewedpages-list' => "Цей список містить [[{{MediaWiki:Validationpage}}|рецензовані]] сторінки, чий ''найвищий досягнутий'' рівень рецензії (або версії) є зазначений рівень.
Посилання веде на останню версію цього рівня.",
	'reviewedpages-none' => 'У цьому списку відсутні сторінки',
	'reviewedpages-lev-0' => 'Переглянута',
	'reviewedpages-lev-1' => 'Якісна',
	'reviewedpages-lev-2' => 'Вибрана',
	'reviewedpages-all' => 'перевірені версії',
	'reviewedpages-best' => 'найкраща версія',
);

/** Urdu (اردو)
 * @author පසිඳු කාවින්ද
 */
$messages['ur'] = array(
	'reviewedpages-best' => 'سب سے بہترین ترمیمی',
);

/** vèneto (vèneto)
 * @author Candalua
 */
$messages['vec'] = array(
	'reviewedpages' => 'Pagine riesaminà',
	'reviewedpages-leg' => 'Elenco de le pagine revisionà',
	'reviewedpages-list' => "Sto elenco contien le [[{{MediaWiki:Validationpage}}|pagine controlà]] el cui massimo livèl de revisione ragiunto (de una revisione) el xe quelo indicà.
Xe fornìo un link a l'ultima revision par quel livèl.",
	'reviewedpages-none' => 'No ghe xe nissuna pagina su sta lista',
	'reviewedpages-lev-0' => 'Rivardà',
	'reviewedpages-lev-1' => 'De qualità',
	'reviewedpages-lev-2' => 'De alta qualità',
	'reviewedpages-all' => 'versioni riesaminà',
	'reviewedpages-best' => 'revision mejo',
);

/** Veps (vepsän kel’)
 * @author Игорь Бродский
 */
$messages['vep'] = array(
	'reviewedpages' => 'Kodvdud lehtpoled',
	'reviewedpages-leg' => 'Kodvdud lehtpoliden nimikirjutez',
	'reviewedpages-list' => 'Necil lehtpolel om [[{{MediaWiki:Validationpage}}|kodvdud]] lehtpoliden nimed; niiden "parahim arvsana" sättub märitud tazopindale.
Kosketuz ozutab necen lehtpolen jäl\'gmäižhe versijaha ningoižen arvsananke.',
	'reviewedpages-none' => 'Neciš nimikirjuteses ei ole lehtpolid',
	'reviewedpages-lev-0' => 'Arvosteldud',
	'reviewedpages-lev-1' => 'Lopuližikš kodvdud',
	'reviewedpages-lev-2' => 'Valitud',
	'reviewedpages-all' => 'kodvdud versijad',
	'reviewedpages-best' => 'parahim versii',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'reviewedpages' => 'Các trang đã duyệt',
	'reviewedpages-leg' => 'Liệt kê các trang đã duyệt',
	'reviewedpages-list' => "Đây là danh sách các trang [[{{MediaWiki:Validationpage}}|duyệt]] có phiên bản được duyệt thành cấp độ ''tối cao'' chỉ định.
Cấp độ có liên kết đến phiên bản gần đây nhất.",
	'reviewedpages-none' => 'Danh sách này không có trang nào',
	'reviewedpages-lev-0' => 'Đã xem qua',
	'reviewedpages-lev-1' => 'Chất lượng',
	'reviewedpages-lev-2' => 'Rất tốt',
	'reviewedpages-all' => 'Phiên bản đã duyệt',
	'reviewedpages-best' => 'phiên bản hạng nhất',
);

/** Volapük (Volapük)
 * @author Smeira
 */
$messages['vo'] = array(
	'reviewedpages' => 'Pads pekrütöl',
	'reviewedpages-none' => 'Lised at labon padis nonik',
	'reviewedpages-lev-1' => 'Kaliet',
	'reviewedpages-all' => 'fomams pekrütöl',
);

/** Cantonese (粵語)
 * @author Shinjiman
 */
$messages['yue'] = array(
	'reviewedpages' => '複審過嘅版',
	'reviewedpages-leg' => '列示複審過到指定級數嘅版', # Fuzzy
	'reviewedpages-list' => '下面嘅版已經複審到一個指定嘅級數', # Fuzzy
	'reviewedpages-none' => '無版響呢個表度',
	'reviewedpages-lev-0' => '視察過',
	'reviewedpages-lev-1' => '質素',
	'reviewedpages-lev-2' => '正',
	'reviewedpages-all' => '複審版',
	'reviewedpages-best' => '最好修訂', # Fuzzy
);

/** Simplified Chinese (中文（简体）‎)
 * @author PhiLiP
 * @author Shinjiman
 */
$messages['zh-hans'] = array(
	'reviewedpages' => '已复审页面',
	'reviewedpages-leg' => '列出已被复审的页面',
	'reviewedpages-list' => "此列表列出了（单个修订）的''最高''复审级别为指定级别的[[{{MediaWiki:Validationpage}}|已复审]]页面。已给出该级别最新修订的链接。",
	'reviewedpages-none' => '此列表中没有页面。',
	'reviewedpages-lev-0' => '视察过',
	'reviewedpages-lev-1' => '质素',
	'reviewedpages-lev-2' => '特色',
	'reviewedpages-all' => '已复审版本',
	'reviewedpages-best' => '最佳修订',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Horacewai2
 * @author Shinjiman
 * @author Waihorace
 */
$messages['zh-hant'] = array(
	'reviewedpages' => '複審過的頁面',
	'reviewedpages-leg' => '列示複審過到指定級數之頁面',
	'reviewedpages-list' => '以下的頁面[[{{MediaWiki:Validationpage}}|已經複審]]到一個指定的級數',
	'reviewedpages-none' => '沒有頁面在這個清單中',
	'reviewedpages-lev-0' => '視察過',
	'reviewedpages-lev-1' => '質素',
	'reviewedpages-lev-2' => '特色',
	'reviewedpages-all' => '複審版',
	'reviewedpages-best' => '最佳修訂',
);
