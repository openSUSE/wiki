<?php
/**
 * Aliases for special pages for extension FlaggedRevs
 *
 * @file
 * @ingroup Extensions
 */

$specialPageAliases = array();

/** English (English) */
$specialPageAliases['en'] = array(
	'PendingChanges' => array( 'PendingChanges', 'OldReviewedPages' /* deprecated */ ),
	'ProblemChanges' => array( 'ProblemChanges' ),
	'QualityOversight' => array( 'AdvancedReviewLog', 'QualityOversight' /* deprecated */ ),
	'ReviewedPages' => array( 'ReviewedPages' ),
	'RevisionReview' => array( 'RevisionReview' ),
	'Stabilization' => array( 'Stabilization', 'Stabilisation' ),
	'StablePages' => array( 'StablePages' ),
	'ConfiguredPages' => array( 'ConfiguredPages' ),
	'ReviewedVersions' => array( 'ReviewedVersions', 'StableVersions' /*old name*/ ),
	'UnreviewedPages' => array( 'UnreviewedPages' ),
	'ValidationStatistics' => array( 'ValidationStatistics' ),
);

/** Aragonese (Aragonés) */
$specialPageAliases['an'] = array(
	'PendingChanges' => array( 'PachinasSupervisatasAntigas' ),
	'QualityOversight' => array( 'SupervisataDeCalidat' ),
	'ReviewedPages' => array( 'PachinasSubervisatas' ),
	'StablePages' => array( 'PachinasEstables' ),
	'UnreviewedPages' => array( 'PachinasNoRevisatas' ),
);

/** Arabic (العربية) */
$specialPageAliases['ar'] = array(
	'PendingChanges' => array( 'صفحات_مراجعة_قديمة' ),
	'ProblemChanges' => array( 'تغييرات_المشاكل' ),
	'QualityOversight' => array( 'سجل_المراجعة_المتقدم', 'نظر_الجودة' ),
	'ReviewedPages' => array( 'صفحات_مراجعة' ),
	'RevisionReview' => array( 'مراجعة_نسخة' ),
	'Stabilization' => array( 'استقرار' ),
	'StablePages' => array( 'صفحات_مستقرة' ),
	'ConfiguredPages' => array( 'صفحات_مضبوطة' ),
	'ReviewedVersions' => array( 'نسخ_مراجعة', 'نسخ_مستقرة' ),
	'UnreviewedPages' => array( 'صفحات_غير_مراجعة' ),
	'ValidationStatistics' => array( 'إحصاءات_التحقق' ),
);

/** Egyptian Spoken Arabic (مصرى) */
$specialPageAliases['arz'] = array(
	'PendingChanges' => array( 'صفح_مراجعه_قديمه' ),
	'ProblemChanges' => array( 'تغييرات_المشاكل' ),
	'QualityOversight' => array( 'مراقبة_الجوده' ),
	'ReviewedPages' => array( 'صفح_مراجعه' ),
	'RevisionReview' => array( 'مراجعة_نسخه' ),
	'Stabilization' => array( 'استقرار' ),
	'StablePages' => array( 'صفح_مستقر' ),
	'ConfiguredPages' => array( 'صفحات_مضبوطه' ),
	'ReviewedVersions' => array( 'نسخ_مراجعه', 'نسخ_مستقره' ),
	'UnreviewedPages' => array( 'صفح_مش_متراجعه' ),
	'ValidationStatistics' => array( 'احصائيات_الصلاحيه' ),
);

/** Southern Balochi (بلوچی مکرانی) */
$specialPageAliases['bcc'] = array(
	'PendingChanges' => array( 'صفحات-بازبینی-قدیمی' ),
	'QualityOversight' => array( 'رویت-کیفیت' ),
	'ReviewedPages' => array( 'صفحات-بازبینی' ),
	'StablePages' => array( 'صفحات-ثابت' ),
	'UnreviewedPages' => array( 'صفحات-بی-بازبینی' ),
);

/** Breton (Brezhoneg) */
$specialPageAliases['br'] = array(
	'Stabilization' => array( 'Stabiladur' ),
	'StablePages' => array( 'PajennoùStabil' ),
	'ConfiguredPages' => array( 'PajennoùKefluniet' ),
	'ValidationStatistics' => array( 'KadarnaatStadegoù' ),
);

/** Bosnian (Bosanski) */
$specialPageAliases['bs'] = array(
	'PendingChanges' => array( 'StarePregledaneStranice' ),
	'QualityOversight' => array( 'KvalitetNadzora' ),
	'ReviewedPages' => array( 'PregledaneStranice' ),
	'RevisionReview' => array( 'PregledRevizija' ),
	'Stabilization' => array( 'Stabilizacija' ),
	'StablePages' => array( 'StabilneStranice' ),
	'UnreviewedPages' => array( 'NeprovjereneStranice' ),
	'ValidationStatistics' => array( 'StatistikeValidacije' ),
);

/** German (Deutsch) */
$specialPageAliases['de'] = array(
	'PendingChanges' => array( 'Seiten_mit_ungesichteten_Versionen' ),
	'QualityOversight' => array( 'Markierungsübersicht' ),
	'ReviewedPages' => array( 'Gesichtete_Seiten' ),
	'RevisionReview' => array( 'Versionsprüfung' ),
	'Stabilization' => array( 'Seitenkonfiguration', 'Stabilisierung' ),
	'StablePages' => array( 'Konfigurierte_Seiten' ),
	'UnreviewedPages' => array( 'Ungesichtete_Seiten' ),
	'ValidationStatistics' => array( 'Markierungsstatistik' ),
);

/** Lower Sorbian (Dolnoserbski) */
$specialPageAliases['dsb'] = array(
	'PendingChanges' => array( 'Zasej njepśeglědane boki' ),
	'QualityOversight' => array( 'Kwalitna_kontrola' ),
	'ReviewedPages' => array( 'Pśeglědane_boki' ),
	'RevisionReview' => array( 'Wersijowe_pśeglědanje' ),
	'Stabilization' => array( 'Stabilizacija' ),
	'StablePages' => array( 'Stabilne_boki' ),
	'UnreviewedPages' => array( 'Njepśeglědane_boki' ),
	'ValidationStatistics' => array( 'Statistika_pśeglědanjow' ),
);

/** Esperanto (Esperanto) */
$specialPageAliases['eo'] = array(
	'PendingChanges' => array( 'Malfreŝe kontrolitaj paĝoj' ),
	'QualityOversight' => array( 'Kvalita_kontrolo' ),
	'ReviewedPages' => array( 'Kontrolitaj_paĝoj' ),
	'Stabilization' => array( 'Stabilado' ),
	'StablePages' => array( 'Stabilaj_paĝoj' ),
	'UnreviewedPages' => array( 'Nekontrolitaj_paĝoj' ),
);

/** Spanish (Español) */
$specialPageAliases['es'] = array(
	'PendingChanges' => array( 'Páginas_revisadas_antiguas' ),
	'ReviewedPages' => array( 'PáginasRevisadas', 'Páginas_revisadas' ),
	'Stabilization' => array( 'Estabilización' ),
	'StablePages' => array( 'Páginas_publicadas' ),
	'ConfiguredPages' => array( 'PáginasConfiguradas', 'Páginas_configuradas' ),
	'ReviewedVersions' => array( 'Versiones_revisadas' ),
	'UnreviewedPages' => array( 'Páginas_sin_revisar' ),
	'ValidationStatistics' => array( 'Estadísticas_de_validación' ),
);

/** Persian (فارسی) */
$specialPageAliases['fa'] = array(
	'PendingChanges' => array( 'صفحه‌های_بازبینی_شده_قدیمی' ),
	'ProblemChanges' => array( 'تغییر_مشکلات' ),
	'QualityOversight' => array( 'نظارت_کیفی' ),
	'ReviewedPages' => array( 'صفحه‌های_بازبینی_شده' ),
	'RevisionReview' => array( 'بازبینی_نسخه' ),
	'Stabilization' => array( 'پایدارسازی' ),
	'StablePages' => array( 'صفحه‌های_پایدار' ),
	'ConfiguredPages' => array( 'صفحه‌های_تنظیم_شده' ),
	'ReviewedVersions' => array( 'نسخه‌های_پایدار' ),
	'UnreviewedPages' => array( 'صفحه‌های‌بازبینی‌نشده' ),
	'ValidationStatistics' => array( 'آمار_تاییدها' ),
);

/** Finnish (Suomi) */
$specialPageAliases['fi'] = array(
	'ProblemChanges' => array( 'Ongelmalliset_muutokset' ),
	'Stabilization' => array( 'Vakaaksi_versioksi' ),
	'StablePages' => array( 'Vakaat_sivut' ),
	'UnreviewedPages' => array( 'Arvioimattomat_sivut' ),
);

/** French (Français) */
$specialPageAliases['fr'] = array(
	'PendingChanges' => array( 'AnciennesPagesRelues' ),
	'QualityOversight' => array( 'SuperviseurQualité' ),
	'ReviewedPages' => array( 'Pages_révisées' ),
	'RevisionReview' => array( 'Relecture_des_révisions' ),
	'StablePages' => array( 'Pages_stables' ),
	'UnreviewedPages' => array( 'Pages_non_relues' ),
	'ValidationStatistics' => array( 'Statistiques_de_validation' ),
);

/** Franco-Provençal (Arpetan) */
$specialPageAliases['frp'] = array(
	'PendingChanges' => array( 'Pâges que les vèrsions sont dèpassâs', 'PâgesQueLesVèrsionsSontDèpassâs' ),
	'QualityOversight' => array( 'Supèrvision_de_qualitât', 'SupèrvisionDeQualitât' ),
	'ReviewedPages' => array( 'Pâges_revues', 'PâgesRevues' ),
	'RevisionReview' => array( 'Rèvision_de_les_vèrsions', 'RèvisionDeLesVèrsions' ),
	'Stabilization' => array( 'Stabilisacion' ),
	'StablePages' => array( 'Pâges_stâbles', 'PâgesStâbles' ),
	'UnreviewedPages' => array( 'Pâges_pas_revues', 'PâgesPasRevues' ),
	'ValidationStatistics' => array( 'Statistiques_de_validacion', 'StatistiquesDeValidacion' ),
);

/** Galician (Galego) */
$specialPageAliases['gl'] = array(
	'PendingChanges' => array( 'Páxinas revisadas hai tempo' ),
	'QualityOversight' => array( 'Revisión de calidade' ),
	'ReviewedPages' => array( 'Páxinas revisadas' ),
	'RevisionReview' => array( 'Revisión da revisión' ),
	'Stabilization' => array( 'Estabilización' ),
	'StablePages' => array( 'Páxinas estábeis' ),
	'UnreviewedPages' => array( 'Páxinas non revisadas' ),
	'ValidationStatistics' => array( 'Estatísticas de validación' ),
);

/** Swiss German (Alemannisch) */
$specialPageAliases['gsw'] = array(
	'PendingChanges' => array( 'Syte mit Versione wu nit gsichtet sin' ),
	'QualityOversight' => array( 'Markierigsibersicht' ),
	'ReviewedPages' => array( 'Gsichteti Syte' ),
	'RevisionReview' => array( 'Versionspriefig' ),
	'Stabilization' => array( 'Sytekonfiguration' ),
	'StablePages' => array( 'Konfigurierti Syte' ),
	'UnreviewedPages' => array( 'Syte wu nit gsichtet sin' ),
	'ValidationStatistics' => array( 'Markierigsstatischtik' ),
);

/** Gujarati (ગુજરાતી) */
$specialPageAliases['gu'] = array(
	'PendingChanges' => array( 'જુનાં તપાસાયેલા પાનાં' ),
	'QualityOversight' => array( 'ગુણવતા_દુર્લક્ષ' ),
	'ReviewedPages' => array( 'રીવ્યુપાનાં' ),
	'RevisionReview' => array( 'આવૃત્તિરીવ્યુ' ),
	'Stabilization' => array( 'સ્થિરતા' ),
	'StablePages' => array( 'સ્થિરપાનાઓ' ),
);

/** Hindi (हिन्दी) */
$specialPageAliases['hi'] = array(
	'PendingChanges' => array( 'पुरानेदेखेंहुएपन्ने' ),
	'QualityOversight' => array( 'गुणवत्ताओव्हरसाईट' ),
	'ReviewedPages' => array( 'जाँचेहुएपन्ने' ),
	'StablePages' => array( 'स्थिरपन्ने' ),
	'UnreviewedPages' => array( 'नदेखेंहुएपन्ने' ),
);

/** Croatian (Hrvatski) */
$specialPageAliases['hr'] = array(
	'StablePages' => array( 'Stabilne_stranice' ),
);

/** Upper Sorbian (Hornjoserbsce) */
$specialPageAliases['hsb'] = array(
	'PendingChanges' => array( 'Zaso njepřehladane strony' ),
	'QualityOversight' => array( 'Kwalitna_kontrola' ),
	'ReviewedPages' => array( 'Přehladane_strony' ),
	'RevisionReview' => array( 'Wersijowe_přehladanje' ),
	'Stabilization' => array( 'Stabilizacija' ),
	'StablePages' => array( 'Stabilne_strony' ),
	'UnreviewedPages' => array( 'Njepřehladane_strony' ),
	'ValidationStatistics' => array( 'Statistika_přehladanjow' ),
);

/** Hungarian (Magyar) */
$specialPageAliases['hu'] = array(
	'PendingChanges' => array( 'Elavult ellenőrzött lapok', 'Régen ellenőrzött lapok' ),
	'QualityOversight' => array( 'Minőségellenőrzés' ),
	'ReviewedPages' => array( 'Ellenőrzött_lapok' ),
	'RevisionReview' => array( 'Változat_ellenőrzése' ),
	'Stabilization' => array( 'Lap_rögzítése' ),
	'StablePages' => array( 'Rögzített_lapok' ),
	'UnreviewedPages' => array( 'Ellenőrizetlen_lapok' ),
	'ValidationStatistics' => array( 'Ellenőrzési_statisztika' ),
);

/** Interlingua (Interlingua) */
$specialPageAliases['ia'] = array(
	'PendingChanges' => array( 'Modificationes_pendente', 'Paginas_revidite_ancian' ),
	'ProblemChanges' => array( 'Modificationes_problematic' ),
	'QualityOversight' => array( 'Registro_de_revision_avantiate', 'Supervision_de_qualitate' ),
	'ReviewedPages' => array( 'Paginas_revidite' ),
	'RevisionReview' => array( 'Revision_de_versiones' ),
	'StablePages' => array( 'Paginas_stabile', 'Paginas_publicate' ),
	'ConfiguredPages' => array( 'Paginas_configurate' ),
	'ReviewedVersions' => array( 'Versiones_revidite', 'Versiones_stabile' ),
	'UnreviewedPages' => array( 'Paginas_non_revidite' ),
	'ValidationStatistics' => array( 'Statisticas_de_validation' ),
);

/** Indonesian (Bahasa Indonesia) */
$specialPageAliases['id'] = array(
	'PendingChanges' => array( 'Halaman_tertinjau_usang', 'HalamanTertinjauUsang' ),
	'ProblemChanges' => array( 'Perubahan_masalah', 'PerubahanMasalah' ),
	'QualityOversight' => array( 'Pemeriksaan_kualitas', 'PemeriksaanKualitas' ),
	'ReviewedPages' => array( 'Halaman_tertinjau', 'HalamanTertinjau' ),
	'RevisionReview' => array( 'Tinjauan_revisi', 'TinjauanRevisi' ),
	'Stabilization' => array( 'Stabilisasi' ),
	'StablePages' => array( 'Halaman_stabil', 'HalamanStabil' ),
	'ConfiguredPages' => array( 'Halaman_terkonfigurasi', 'HalamanTerkonfigurasi' ),
	'ReviewedVersions' => array( 'Versi_tertinjau', 'VersiTertinjau', 'Versi_stabil', 'VersiStabil' ),
	'UnreviewedPages' => array( 'Halaman_yang_belum_ditinjau', 'HalamanBelumDitinjau' ),
	'ValidationStatistics' => array( 'Statistik_validasi', 'StatistikValidasi' ),
);

/** Japanese (日本語) */
$specialPageAliases['ja'] = array(
	'PendingChanges' => array( '古くなった査読済みページ' ),
	'ProblemChanges' => array( '問題の修正' ),
	'QualityOversight' => array( '品質監督' ),
	'ReviewedPages' => array( '査読済みページ' ),
	'RevisionReview' => array( '特定版の査読' ),
	'Stabilization' => array( '固定', '採択', 'ページの採択' ),
	'StablePages' => array( '固定ページ', '安定ページ', '採用ページ' ),
	'ConfiguredPages' => array( '査読設定のあるページ' ),
	'ReviewedVersions' => array( '固定版', '安定版', '採用版' ),
	'UnreviewedPages' => array( '未査読ページ', '査読待ちページ' ),
	'ValidationStatistics' => array( '判定統計' ),
);

/** Colognian (Ripoarisch) */
$specialPageAliases['ksh'] = array(
	'PendingChanges' => array( 'SiggeMetUnjesichVersione' ),
	'ProblemChanges' => array( 'SiggeMetProbleme' ),
	'ReviewedPages' => array( 'JesichSigge' ),
	'UnreviewedPages' => array( 'UNjesichSigge' ),
);

/** Ladino (Ladino) */
$specialPageAliases['lad'] = array(
	'ProblemChanges' => array( 'Trocamientos_de_problemes' ),
	'QualityOversight' => array( 'Sorvelyança_de_calidad' ),
	'ReviewedPages' => array( 'HojasEgzaminadas' ),
	'RevisionReview' => array( 'Egzamén_de_rēvizyones' ),
	'Stabilization' => array( 'Estabilizasyón' ),
	'StablePages' => array( 'HojasEstables' ),
	'ConfiguredPages' => array( 'HojasArregladas' ),
	'ReviewedVersions' => array( 'VersionesEgzaminadas' ),
	'UnreviewedPages' => array( 'HojasNoEgzaminadas' ),
	'ValidationStatistics' => array( 'Estatistikas_de_validdasyón' ),
);

/** Luxembourgish (Lëtzebuergesch) */
$specialPageAliases['lb'] = array(
	'PendingChanges' => array( 'Säite_mat_Versiounen_déi_net_iwwerpréift_sinn' ),
	'ProblemChanges' => array( 'Problematesch_Ännerungen' ),
	'ReviewedPages' => array( 'Säiten_déi_iwwerkuckt_goufen' ),
	'RevisionReview' => array( 'Versioun_iwwerpréifen' ),
	'Stabilization' => array( 'Stabilisatioun' ),
	'StablePages' => array( 'Stabil_Säiten' ),
	'ReviewedVersions' => array( 'Stabil_Versiounen' ),
	'UnreviewedPages' => array( 'Net_iwwerpréifte_Säiten' ),
	'ValidationStatistics' => array( 'Statistik_vun_den_iwwerpréifte_Säiten' ),
);

/** Macedonian (Македонски) */
$specialPageAliases['mk'] = array(
	'PendingChanges' => array( 'СтариОценетиСтраници' ),
	'ProblemChanges' => array( 'ПромениНаПроблеми' ),
	'QualityOversight' => array( 'НадлегувањеНаКвалитетот' ),
	'ReviewedPages' => array( 'ПрегледаниСтраници' ),
	'RevisionReview' => array( 'ПрегледНаРевизии' ),
	'Stabilization' => array( 'Стабилизација' ),
	'StablePages' => array( 'СтабилниСтраници' ),
	'ReviewedVersions' => array( 'ПрегледаниВерзии', 'СтабилниВерзии' ),
	'UnreviewedPages' => array( 'НепрегледаниСтраници' ),
	'ValidationStatistics' => array( 'ВалидацискиСтатистики' ),
);

/** Malayalam (മലയാളം) */
$specialPageAliases['ml'] = array(
	'PendingChanges' => array( 'മുമ്പ്_സംശോധനം_ചെയ്ത_താളുകൾ' ),
	'ProblemChanges' => array( 'പ്രശ്നകാരിമാറ്റങ്ങൾ' ),
	'QualityOversight' => array( 'ഗുണമേന്മാമേൽനോട്ടം' ),
	'ReviewedPages' => array( 'സംശോധനംചെയ്തതാളുകൾ' ),
	'RevisionReview' => array( 'നാൾപ്പതിപ്പ്സംശോധനം' ),
	'Stabilization' => array( 'സ്ഥിരപ്പെടുത്തൽ' ),
	'StablePages' => array( 'സ്ഥിരതാളുകൾ' ),
	'ConfiguredPages' => array( 'ക്രമീകരിച്ചതാളുകൾ' ),
	'ReviewedVersions' => array( 'സംശോധിതപതിപ്പുകൾ', 'സ്ഥിരതയുള്ള_പതിപ്പുകൾ' ),
	'UnreviewedPages' => array( 'സംശോധനംചെയ്യാത്തതാളുകൾ' ),
	'ValidationStatistics' => array( 'മൂല്യനിർണ്ണയസ്ഥിതിവിവരം' ),
);

/** Marathi (मराठी) */
$specialPageAliases['mr'] = array(
	'PendingChanges' => array( 'जुनीतपासलेलीपाने' ),
	'QualityOversight' => array( 'गुणवत्ताओव्हरसाईट' ),
	'ReviewedPages' => array( 'तपासलेलीपाने' ),
	'RevisionReview' => array( 'आवृत्तीसमीक्षा' ),
	'Stabilization' => array( 'स्थिरीकरण' ),
	'StablePages' => array( 'स्थिरपाने' ),
	'UnreviewedPages' => array( 'नतपासलेलीपाने' ),
);

/** Malay (Bahasa Melayu) */
$specialPageAliases['ms'] = array(
	'PendingChanges' => array( 'Laman_diperiksa_lapuk' ),
	'QualityOversight' => array( 'Kawalan_mutu' ),
	'ReviewedPages' => array( 'Laman_diperiksa' ),
	'StablePages' => array( 'Laman_stabil' ),
	'UnreviewedPages' => array( 'Laman_tidak_diperiksa' ),
);

/** Nedersaksisch (Nedersaksisch) */
$specialPageAliases['nds-nl'] = array(
	'PendingChanges' => array( 'Pagina\'s_verouwerde_eindredactie' ),
	'ProblemChanges' => array( 'Preblematische_wiezigingen' ),
	'QualityOversight' => array( 'Kwaliteitscontrole' ),
	'ReviewedPages' => array( 'Pagina_mit_eindredactie' ),
	'RevisionReview' => array( 'Eindredactie_versies' ),
	'Stabilization' => array( 'Stabilisasie' ),
	'StablePages' => array( 'Stebiele_pagina\'s' ),
	'UnreviewedPages' => array( 'Pagina\'s_zonder_eindredactie' ),
	'ValidationStatistics' => array( 'Eindredactiestaotestieken' ),
);

/** Dutch (Nederlands) */
$specialPageAliases['nl'] = array(
	'PendingChanges' => array( 'PaginasVerouderdeEindredactie', 'Pagina\'sVerouderdeEindredactie' ),
	'ProblemChanges' => array( 'ProblematischeWijzigingen' ),
	'QualityOversight' => array( 'KwaliteitsControle' ),
	'ReviewedPages' => array( 'PaginasMetEindredactie', 'Pagina\'sMetEindredactie' ),
	'RevisionReview' => array( 'EindredactieVersies' ),
	'Stabilization' => array( 'Stabilisatie' ),
	'StablePages' => array( 'StabielePaginas', 'StabielePagina\'s' ),
	'ConfiguredPages' => array( 'IngesteldePaginas', 'IngesteldePagina\'s' ),
	'ReviewedVersions' => array( 'GecontroleerdeVersies', 'StabieleVersies' ),
	'UnreviewedPages' => array( 'PaginasZonderEindredactie', 'Pagina\'sZonderEindredactie' ),
	'ValidationStatistics' => array( 'Eindredactiestatistieken', 'StatistiekenEindredactie' ),
);

/** Norwegian Nynorsk (‪Norsk (nynorsk)‬) */
$specialPageAliases['nn'] = array(
	'PendingChanges' => array( 'Gamle vurderte sider' ),
	'QualityOversight' => array( 'Kvalitetsoversyn' ),
	'ReviewedPages' => array( 'Vurderte_sider' ),
	'RevisionReview' => array( 'Versjonsvurdering' ),
	'Stabilization' => array( 'Stabilisering' ),
	'StablePages' => array( 'Stabile_sider' ),
	'UnreviewedPages' => array( 'Ikkje-vurderte_sider' ),
	'ValidationStatistics' => array( 'Valideringsstatistikk' ),
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬) */
$specialPageAliases['no'] = array(
	'PendingChanges' => array( 'Gamle anmeldte sider' ),
	'ProblemChanges' => array( 'Problemendringer' ),
	'QualityOversight' => array( 'Kvalitetsoversikt' ),
	'ReviewedPages' => array( 'Anmeldte_sider' ),
	'RevisionReview' => array( 'Revisjonsgjennomgang' ),
	'Stabilization' => array( 'Stabilisering' ),
	'StablePages' => array( 'Stabile_sider' ),
	'ConfiguredPages' => array( 'Konfigurerte_sider' ),
	'ReviewedVersions' => array( 'Gjennomgåtte_sider' ),
	'UnreviewedPages' => array( 'Ikke-gjennomgåtte_sider' ),
	'ValidationStatistics' => array( 'Valideringsstatistikk' ),
);

/** Occitan (Occitan) */
$specialPageAliases['oc'] = array(
	'PendingChanges' => array( 'PaginasAncianasRelegidas' ),
	'QualityOversight' => array( 'SupervisorQualitat' ),
	'ReviewedPages' => array( 'Paginas_revisadas', 'PaginasRevisadas' ),
	'RevisionReview' => array( 'Relectura_de_las_revisions' ),
	'StablePages' => array( 'Paginas_establas', 'PaginasEstablas' ),
	'UnreviewedPages' => array( 'Paginas_pas_relegidas', 'PaginasPasRelegidas' ),
);

/** Polish (Polski) */
$specialPageAliases['pl'] = array(
	'PendingChanges' => array( 'Zdezaktualizowane_przejrzane_strony' ),
	'ProblemChanges' => array( 'Wątpliwe_zmiany' ),
	'QualityOversight' => array( 'Rejestr_oznaczania_wersji' ),
	'ReviewedPages' => array( 'Przejrzane_strony' ),
	'RevisionReview' => array( 'Oznaczenie_wersji' ),
	'Stabilization' => array( 'Konfiguracja_strony' ),
	'StablePages' => array( 'Strony_stabilizowane', 'Strony_z_domyślnie_pokazywaną_wersją_oznaczoną' ),
	'ConfiguredPages' => array( 'Skonfigurowane_strony' ),
	'ReviewedVersions' => array( 'Przejrzane_wersje' ),
	'UnreviewedPages' => array( 'Nieprzejrzane_strony' ),
	'ValidationStatistics' => array( 'Statystyki_oznaczania' ),
);

/** Portuguese (Português) */
$specialPageAliases['pt'] = array(
	'PendingChanges' => array( 'Páginas analisadas antigas' ),
	'QualityOversight' => array( 'Controlo_de_qualidade' ),
	'ReviewedPages' => array( 'Páginas_analisadas' ),
	'RevisionReview' => array( 'Revisão_de_versões' ),
	'Stabilization' => array( 'Estabilização' ),
	'StablePages' => array( 'Páginas_estáveis' ),
	'ReviewedVersions' => array( 'Versões_revistas' ),
	'UnreviewedPages' => array( 'Páginas_a_analisar' ),
	'ValidationStatistics' => array( 'Estatísticas_de_validação' ),
);

/** Brazilian Portuguese (Português do Brasil) */
$specialPageAliases['pt-br'] = array(
	'PendingChanges' => array( 'Versões_antigas_de_páginas_analisadas' ),
	'QualityOversight' => array( 'Observatório_da_qualidade' ),
	'ReviewedPages' => array( 'Páginas_analisadas' ),
	'RevisionReview' => array( 'Revisão_de_edições' ),
	'Stabilization' => array( 'Estabilização' ),
	'StablePages' => array( 'Páginas_estáveis' ),
	'ConfiguredPages' => array( 'Páginas_configuradas' ),
	'UnreviewedPages' => array( 'Páginas_a_analisar' ),
	'ValidationStatistics' => array( 'Estatísticas_de_validação' ),
);

/** Sanskrit (संस्कृत) */
$specialPageAliases['sa'] = array(
	'PendingChanges' => array( 'पूर्वतनआवलोकीतपृष्ठ:' ),
	'QualityOversight' => array( 'गुणपूर्णवृजावलोकन' ),
	'ReviewedPages' => array( 'समसमीक्षीतपृष्ठ:' ),
	'RevisionReview' => array( 'आवृत्तीसमसमीक्षा' ),
	'Stabilization' => array( 'स्वास्थ्य' ),
	'StablePages' => array( 'स्वस्थपृष्ठ' ),
	'UnreviewedPages' => array( 'असमसमीक्षीतपृष्ठ:' ),
	'ValidationStatistics' => array( 'उपयोगितासिद्धीसांख्यिकी' ),
);

/** Slovak (Slovenčina) */
$specialPageAliases['sk'] = array(
	'PendingChanges' => array( 'StaréSkontrolovanéStránky' ),
	'ProblemChanges' => array( 'ProblematickéZmeny' ),
	'QualityOversight' => array( 'DohľadNadKvalitou' ),
	'ReviewedPages' => array( 'SkontrolovanéStránky' ),
	'RevisionReview' => array( 'KontrolaKontroly' ),
	'Stabilization' => array( 'Stabilizácia' ),
	'StablePages' => array( 'StabilnéStránky' ),
	'UnreviewedPages' => array( 'NeskontrolovanéStránky' ),
	'ValidationStatistics' => array( 'ŠtatistikaOverovania' ),
);

/** Albanian (Shqip) */
$specialPageAliases['sq'] = array(
	'Stabilization' => array( 'Stabilizim' ),
	'StablePages' => array( 'FaqetStabile' ),
);

/** Serbian Cyrillic ekavian (Српски (ћирилица)) */
$specialPageAliases['sr-ec'] = array(
	'PendingChanges' => array( 'СтареПрегледанеСтране' ),
	'QualityOversight' => array( 'НадгледањеКвалитета' ),
	'ReviewedPages' => array( 'ПрегледанеСтране' ),
	'StablePages' => array( 'СтабилнеСтране' ),
	'UnreviewedPages' => array( 'НепрегледанеСтране' ),
);

/** Swedish (Svenska) */
$specialPageAliases['sv'] = array(
	'PendingChanges' => array( 'Gamla granskade sidor' ),
	'QualityOversight' => array( 'Kvalitetsöversikt' ),
	'ReviewedPages' => array( 'Granskade_sidor' ),
	'RevisionReview' => array( 'Versionsgranskning' ),
	'Stabilization' => array( 'Stabilisering' ),
	'StablePages' => array( 'Stabila_sidor' ),
	'UnreviewedPages' => array( 'Ogranskade_sidor' ),
	'ValidationStatistics' => array( 'Valideringsstatistik' ),
);

/** Swahili (Kiswahili) */
$specialPageAliases['sw'] = array(
	'PendingChanges' => array( 'KurasaZilizoonyeshwaAwali' ),
	'ReviewedPages' => array( 'OnyeshaKurasa' ),
	'Stabilization' => array( 'Uimalishaji' ),
	'StablePages' => array( 'KurasaImara' ),
	'UnreviewedPages' => array( 'KurasaZisizoonyeshwa' ),
	'ValidationStatistics' => array( 'TakwimuIliyosahihi' ),
);

/** Tagalog (Tagalog) */
$specialPageAliases['tl'] = array(
	'PendingChanges' => array( 'Nasuring lumang mga pahina' ),
	'QualityOversight' => array( 'Maingat na pamamahala ng kalidad' ),
	'ReviewedPages' => array( 'Sinuring mga pahina' ),
	'RevisionReview' => array( 'Pagsusuri ng pagbabago' ),
	'Stabilization' => array( 'Pagpapatatag', 'Pagpapatibay' ),
	'StablePages' => array( 'Matatag na mga pahina' ),
	'UnreviewedPages' => array( 'Mga pahina hindi pa nasusuri' ),
	'ValidationStatistics' => array( 'Mga estadistika ng pagtitiyak' ),
);

/** Turkish (Türkçe) */
$specialPageAliases['tr'] = array(
	'PendingChanges' => array( 'EskiİncelenmişSayfalar' ),
	'ProblemChanges' => array( 'ProblemDeğişiklikleri' ),
	'QualityOversight' => array( 'KaliteGözetimi' ),
	'ReviewedPages' => array( 'İncelenmişSayfalar' ),
	'RevisionReview' => array( 'Sürümİnceleme' ),
	'Stabilization' => array( 'Stabilizasyon', 'İstikrar' ),
	'StablePages' => array( 'StabilSayfalar', 'İstikrarlıSayfalar' ),
	'ReviewedVersions' => array( 'İncelenmişSürümler', 'StabilSürümler' ),
	'UnreviewedPages' => array( 'İncelenmemişSayfalar' ),
	'ValidationStatistics' => array( 'Doğrulamaİstatistikleri' ),
);

/** Татарча (Татарча) */
$specialPageAliases['tt-cyrl'] = array(
	'StablePages' => array( 'Тотрыклы_битләр' ),
);

/** Vèneto (Vèneto) */
$specialPageAliases['vec'] = array(
	'PendingChanges' => array( 'PagineRiesaminàVèce' ),
	'QualityOversight' => array( 'ControloQualità' ),
	'ReviewedPages' => array( 'PagineRiesaminà' ),
	'StablePages' => array( 'PagineStabili' ),
	'UnreviewedPages' => array( 'PagineNonRiesaminà' ),
	'ValidationStatistics' => array( 'StatìstegheDeValidassion' ),
);

/** Vietnamese (Tiếng Việt) */
$specialPageAliases['vi'] = array(
	'PendingChanges' => array( 'Trang chưa duyệt cũ' ),
	'QualityOversight' => array( 'Giám_sát_chất_lượng' ),
	'ReviewedPages' => array( 'Trang_đã_duyệt' ),
	'StablePages' => array( 'Trang_ổn_định' ),
	'UnreviewedPages' => array( 'Trang_chưa_duyệt' ),
);

/** Traditional Chinese (‪中文(繁體)‬) */
$specialPageAliases['zh-hant'] = array(
	'ProblemChanges' => array( '問題改變' ),
	'QualityOversight' => array( '進階審閱日誌' ),
	'ReviewedPages' => array( '已審閱頁面' ),
	'RevisionReview' => array( '版本審核' ),
	'StablePages' => array( '穩定頁面' ),
	'ConfiguredPages' => array( '頁面審核設定' ),
	'ReviewedVersions' => array( '穩定版本' ),
	'UnreviewedPages' => array( '未審閱頁面' ),
);

/**
 * For backwards compatibility with MediaWiki 1.15 and earlier.
 */
$aliases =& $specialPageAliases;