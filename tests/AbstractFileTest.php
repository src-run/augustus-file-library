<?php

/*
 * This file is part of the `src-run/augustus-file-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\File\Tests;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SR\File\AbstractFile;
use SR\File\FileInterface;
use SR\File\FileTemp;
use SR\File\Metadata\Guesser\Extension\ExtensionGuesser;
use SR\File\Metadata\Guesser\MediaType\MediaTypeGuesser;
use SR\Interpreter\Interpreter;

/**
 * @coversNothing
 */
abstract class AbstractFileTest extends TestCase
{
    /**
     * @var string
     */
    protected static $functionRoot;

    /**
     * @var string
     */
    protected $functionalPath;

    /**
     * Setup our virtual filesystem environment.
     */
    protected function setUp(): void
    {
        if (null === self::$functionRoot) {
            self::$functionRoot = sprintf('%s/augustus-file-library_abstract-file/', sys_get_temp_dir());
        }

        self::createDirectoryRecursive(
            $testRoot = sprintf('%s/test-%d', self::$functionRoot, mt_rand(10000, 99999))
        );

        $this->functionalPath = $testRoot;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (null !== $this->functionalPath && file_exists($this->functionalPath)) {
            self::removeDirectoryRecursive($this->functionalPath);
        }

        if (null !== self::$functionRoot && mb_strlen(self::$functionRoot) > mb_strlen(sys_get_temp_dir()) && file_exists(self::$functionRoot)) {
            self::removeDirectoryRecursive(self::$functionRoot);
        }
    }

    /**
     * @return string[]
     */
    public static function getRandomContentData(int $limit = 40, bool $shuffle = true): array
    {
        $examples = array_merge(self::getLanguageExcerptsData(), self::getLanguageTranslationsData());
        $shuffler = function () use ($shuffle, $examples) {
            return $shuffle ? (function () use ($examples) {
                $keys = array_keys($examples);
                shuffle($keys);

                return array_combine($keys, array_map(function ($key) use ($examples) {
                    return $examples[$key];
                }, $keys));
            })() : $examples;
        };

        return $limit ? array_slice($shuffler(), 0, $limit, true) : $shuffler();
    }

    /**
     * @return string[]
     */
    protected static function getLanguageExcerptsData(): array
    {
        return [
            'From the Anglo-Saxon Rune Poem [Rune]' => "ᚠᛇᚻ᛫ᛒᛦᚦ᛫ᚠᚱᚩᚠᚢᚱ᛫ᚠᛁᚱᚪ᛫ᚷᛖᚻᚹᛦᛚᚳᚢᛗ\nᛋᚳᛖᚪᛚ᛫ᚦᛖᚪᚻ᛫ᛗᚪᚾᚾᚪ᛫ᚷᛖᚻᚹᛦᛚᚳ᛫ᛗᛁᚳᛚᚢᚾ᛫ᚻᛦᛏ᛫ᛞᚫᛚᚪᚾ\nᚷᛁᚠ᛫ᚻᛖ᛫ᚹᛁᛚᛖ᛫ᚠᚩᚱ᛫ᛞᚱᛁᚻᛏᚾᛖ᛫ᛞᚩᛗᛖᛋ᛫ᚻᛚᛇᛏᚪᚾ᛬",
            'From Laȝamon\'s Brut (The Chronicles of England) [Middle English, West Midlands]' => "An preost wes on leoden, Laȝamon was ihoten\nHe wes Leovenaðes sone -- liðe him be Drihten.\nHe wonede at Ernleȝe at æðelen are chirechen,\nUppen Sevarne staþe, sel þar him þuhte,\nOnfest Radestone, þer he bock radde.",
            'From the Tagelied of Wolfram von Eschenbach [Middle High German]', 'Middle High German' => "Sîne klâwen durh die wolken sint geslagen,\ner stîget ûf mit grôzer kraft,\nich sih in grâwen tägelîch als er wil tagen,\nden tac, der im geselleschaft\nerwenden wil, dem werden man,\nden ich mit sorgen în verliez.\nich bringe in hinnen, ob ich kan.\nsîn vil manegiu tugent michz leisten hiez.",
            'The first stanza of Pushkin\'s Bronze Horseman [Russian]' => "На берегу пустынных волн\nСтоял он, дум великих полн,\nИ вдаль глядел. Пред ним широко\nРека неслася; бедный чёлн\nПо ней стремился одиноко.\nПо мшистым, топким берегам\nЧернели избы здесь и там,\nПриют убогого чухонца;\nИ лес, неведомый лучам\nВ тумане спрятанного солнца,\nКругом шумел.",
            'Šota Rustaveli\'s Veṗxis Ṭq̇aosani, ̣︡Th, The Knight in the Tiger\'s Skin [Georgian]' => "ეპხის ტყაოსანი შოთა რუსთაველი\nღმერთსი შემვედრე, ნუთუ კვლა დამხსნას სოფლისა შრომასა, ცეცხლს, წყალსა და მიწასა, ჰაერთა თანა მრომასა; მომცნეს ფრთენი და აღვფრინდე, მივჰხვდე მას ჩემსა ნდომასა, დღისით და ღამით ვჰხედვიდე მზისა ელვათა კრთომაასა.",
            'Tamil poetry of Subramaniya Bharathiyar: சுப்ரமணிய பாரதியார் (1882-1921)' => "யாமறிந்த மொழிகளிலே தமிழ்மொழி போல் இனிதாவது எங்கும் காணோம், \nபாமரராய் விலங்குகளாய், உலகனைத்தும் இகழ்ச்சிசொலப் பான்மை கெட்டு, \nநாமமது தமிழரெனக் கொண்டு இங்கு வாழ்ந்திடுதல் நன்றோ? சொல்லீர்!\nதேமதுரத் தமிழோசை உலகமெலாம் பரவும்வகை செய்தல் வேண்டும்.",
            'Kannada poetry by Kuvempu — ಬಾ ಇಲ್ಲಿ ಸಂಭವಿಸು' => "ಬಾ ಇಲ್ಲಿ ಸಂಭವಿಸು ಇಂದೆನ್ನ ಹೃದಯದಲಿ \nನಿತ್ಯವೂ ಅವತರಿಪ ಸತ್ಯಾವತಾರ\nಮಣ್ಣಾಗಿ ಮರವಾಗಿ ಮಿಗವಾಗಿ ಕಗವಾಗೀ... \nಮಣ್ಣಾಗಿ ಮರವಾಗಿ ಮಿಗವಾಗಿ ಕಗವಾಗಿ \nಭವ ಭವದಿ ಭತಿಸಿಹೇ ಭವತಿ ದೂರ \nನಿತ್ಯವೂ ಅವತರಿಪ ಸತ್ಯಾವತಾರ || ಬಾ ಇಲ್ಲಿ ||",
        ];
    }

    /**
     * @return string[]
     */
    protected static function getLanguageTranslationsData(): array
    {
        return [
            '(Ki)Swahili' => 'Naweza kula bilauri na sikunyui.',
            'Afrikaans' => 'Ek kan glas eet, maar dit doen my nie skade nie.',
            'Albanian' => 'Unë mund të ha qelq dhe nuk më gjen gjë.',
            'Allemannisch' => 'I kaun Gloos essen, es tuat ma ned weh.',
            'Anglo-Saxon (Latin)' => 'Ic mæg glæs eotan ond hit ne hearmiað me.',
            'Anglo-Saxon (Runes)' => 'ᛁᚳ᛫ᛗᚨᚷ᛫ᚷᛚᚨᛋ᛫ᛖᚩᛏᚪᚾ᛫ᚩᚾᛞ᛫ᚻᛁᛏ᛫ᚾᛖ᛫ᚻᛖᚪᚱᛗᛁᚪᚧ᛫ᛗᛖ᛬',
            'Arabic' => 'أنا قادر على أكل الزجاج و هذا لا يؤلمني.',
            'Aragonés' => 'Puedo minchar beire, no me\'n fa mal .',
            'Armenian' => 'Կրնամ ապակի ուտել և ինծի անհանգիստ չըներ։',
            'Bangla / Bengali' => 'আমি কাঁচ খেতে পারি, তাতে আমার কোনো ক্ষতি হয় না।',
            'Basque' => 'Kristala jan dezaket, ez dit minik ematen.',
            'Bayrisch / Bavarian' => 'I koh Glos esa, und es duard ma ned wei.',
            'Belarusian (Cyrillic)' => 'Я магу есці шкло, яно мне не шкодзіць.',
            'Belarusian (Lacinka)' => 'Ja mahu jeści škło, jano mne ne škodzić.',
            'Bislama' => 'Mi save kakae glas, hemi no save katem mi.',
            'Bosnian, Croatian, Montenegrin and Serbian (Latin)' => 'Ja mogu jesti staklo, i to mi ne šteti.',
            'Bosnian, Montenegrin and Serbian (Cyrillic)' => 'Ја могу јести стакло, и то ми не штети.',
            'Brazilian Portuguese' => 'Posso comer vidro, não me machuca.',
            'Bulgarian' => 'Мога да ям стъкло, то не ми вреди.',
            'Burmese' => 'က္ယ္ဝန္‌တော္‌၊က္ယ္ဝန္‌မ မ္ယက္‌စားနုိင္‌သည္‌။ ၎က္ရောင္‌့ ထိခုိက္‌မ္ဟု မရ္ဟိပာ။',
            'Caboverdiano/Kabuverdianu (Cape Verde)' => 'M\' podê cumê vidru, ca ta maguâ-m\'.',
            'Catalan / Català' => 'Puc menjar vidre, que no em fa mal.',
            'Chamorro' => 'Siña yo\' chumocho krestat, ti ha na\'lalamen yo\'.',
            'Chinese (Traditional)' => '我能吞下玻璃而不傷身體。',
            'Chinese' => '我能吞下玻璃而不伤身体。',
            'Chinook Jargon' => 'Naika məkmək kakshət labutay, pi weyk ukuk munk-sik nay.',
            'Classical Greek' => 'ὕαλον ϕαγεῖν δύναμαι· τοῦτο οὔ με βλάπτει.',
            'Cornish' => 'Mý a yl dybry gwéder hag éf ny wra ow ankenya.',
            'Czech' => 'Mohu jíst sklo, neublíží mi.',
            'Dansk / Danish' => 'Jeg kan spise glas, det gør ikke ondt på mig.',
            'Deutsch (Voralberg)' => 'I ka glas eassa, ohne dass mar weh tuat.',
            'Deutsch / German' => 'Ich kann Glas essen, ohne mir zu schaden.',
            'English (Braille)' => '⠊⠀⠉⠁⠝⠀⠑⠁⠞⠀⠛⠇⠁⠎⠎⠀⠁⠝⠙⠀⠊⠞⠀⠙⠕⠑⠎⠝⠞⠀⠓⠥⠗⠞⠀⠍⠑',
            'English (IPA) (Received Pronunciation)' => '[aɪ kæn iːt glɑːs ænd ɪt dɐz nɒt hɜːt miː]',
            'English' => 'I can eat glass and it doesn\'t hurt me.',
            'Erzian' => 'Мон ярсан суликадо, ды зыян эйстэнзэ а ули.',
            'Esperanto' => 'Mi povas manĝi vitron, ĝi ne damaĝas min.',
            'Estonian' => 'Ma võin klaasi süüa, see ei tee mulle midagi.',
            'European Portuguese' => 'Posso comer vidro, não me faz mal.',
            'Farsi / Persian' => '.من می توانم بدونِ احساس درد شيشه بخورم',
            'Fijian' => 'Au rawa ni kana iloilo, ia au sega ni vakacacani kina.',
            'French' => 'Je peux manger du verre, ça ne me fait pas mal.',
            'Frysk / Frisian' => 'Ik kin glês ite, it docht me net sear.',
            'Føroyskt / Faroese' => 'Eg kann eta glas, skaðaleysur.',
            'Galician' => 'Eu podo xantar cristais e non cortarme.',
            'Georgian' => 'მინას ვჭამ და არა მტკივა.',
            'Gothic' => 'ЌЌЌ ЌЌЌЍ Ќ̈ЍЌЌ, ЌЌ ЌЌЍ ЍЌ ЌЌЌЌ ЌЍЌЌЌЌЌ.',
            'Greek (monotonic)' => 'Μπορώ να φάω σπασμένα γυαλιά χωρίς να πάθω τίποτα.',
            'Greek (polytonic)' => 'Μπορῶ νὰ φάω σπασμένα γυαλιὰ χωρὶς νὰ πάθω τίποτα.',
            'Hausa (Ajami)' => 'إِنا إِىَ تَونَر غِلَاشِ كُمَ إِن غَمَا لَافِىَا',
            'Hausa (Latin)' => 'Inā iya taunar gilāshi kuma in gamā lāfiyā.',
            'Hawaiian' => 'Hiki iaʻu ke ʻai i ke aniani; ʻaʻole nō lā au e ʻeha.',
            'Hebrew' => 'אני יכול לאכול זכוכית וזה לא מזיק לי.',
            'Hindi' => 'मैं काँच खा सकता हूँ और मुझे उससे कोई चोट नहीं पहुंचती.',
            'Hungarian' => 'Meg tudom enni az üveget, nem lesz tőle bajom.',
            'Inuktitut' => 'ᐊᓕᒍᖅ ᓂᕆᔭᕌᖓᒃᑯ ᓱᕋᙱᑦᑐᓐᓇᖅᑐᖓ',
            'Irish' => 'Is féidir liom gloinne a ithe. Ní dhéanann sí dochar ar bith dom.',
            'Italian' => 'Posso mangiare il vetro e non mi fa male.',
            'Jamaican' => 'Mi kian niam glas han i neba hot mi.',
            'Japanese' => '私はガラスを食べられます。それは私を傷つけません。',
            'Javanese' => 'Aku isa mangan beling tanpa lara.',
            'Kannada' => 'ನನಗೆ ಹಾನಿ ಆಗದೆ, ನಾನು ಗಜನ್ನು ತಿನಬಹುದು',
            'Khmer' => 'ខ្ញុំអាចញុំកញ្ចក់បាន ដោយគ្មានបញ្ហារ',
            'Kirchröadsj/Bôchesserplat' => 'Iech ken glaas èèse, mer \'t deet miech jing pieng.',
            'Korean' => '나는 유리를 먹을 수 있어요. 그래도 아프지 않아요',
            'Kreyòl Ayisyen (Haitï)' => 'Mwen kap manje vè, li pa blese\'m.',
            'Lalland Scots / Doric' => 'Ah can eat gless, it disnae hurt us.',
            'Langenfelder Platt' => 'Isch kann Jlaas kimmeln, uuhne datt mich datt weh dääd.',
            'Lao' => 'ຂອ້ຍກິນແກ້ວໄດ້ໂດຍທີ່ມັນບໍ່ໄດ້ເຮັດໃຫ້ຂອ້ຍເຈັບ.',
            'Latin' => 'Vitrum edere possum; mihi non nocet.',
            'Latvian' => 'Es varu ēst stiklu, tas man nekaitē.',
            'Lausitzer Mundart ("Lusatian")' => 'Ich koann Gloos assn und doas dudd merr ni wii.',
            'Lingala' => 'Nakokí kolíya biténi bya milungi, ekosála ngáí mabé tɛ́.',
            'Lithuanian' => 'Aš galiu valgyti stiklą ir jis manęs nežeidžia',
            'Lojban' => 'mi kakne le nu citka le blaci .iku\'i le se go\'i na xrani mi',
            'Lëtzebuergescht / Luxemburgish' => 'Ech kan Glas iessen, daat deet mir nët wei.',
            'Macedonian' => 'Можам да јадам стакло, а не ме штета.',
            'Malay' => 'Saya boleh makan kaca dan ia tidak mencederakan saya.',
            'Maltese' => 'Nista\' niekol il-ħġieġ u ma jagħmilli xejn.',
            'Manx Gaelic' => 'Foddym gee glonney agh cha jean eh gortaghey mee.',
            'Marathi' => 'मी काच खाऊ शकतो, मला ते दुखत नाही.',
            'Marquesan' => 'E koʻana e kai i te karahi, mea ʻā, ʻaʻe hauhau.',
            'Middle English' => 'Ich canne glas eten and hit hirtiþ me nouȝt.',
            'Milanese' => 'Sôn bôn de magnà el véder, el me fa minga mal.',
            'Mongolian (Cyrillic)' => 'Би шил идэй чадна, надад хортой биш ᠪᠢᠰᠢ',
            'Napoletano' => 'M\' pozz magna\' o\'vetr, e nun m\' fa mal.',
            'Navajo' => 'Tsésǫʼ yishą́ągo bííníshghah dóó doo shił neezgai da.',
            'Nederlands / Dutch' => 'Ik kan glas eten, het doet mĳ geen kwaad.',
            'Nepali' => '﻿म काँच खान सक्छू र मलाई केहि नी हुन्‍न् ।',
            'Norsk / Norwegian (Bokmål)' => 'Jeg kan spise glass uten å skade meg.',
            'Norsk / Norwegian (Nynorsk)' => 'Eg kan eta glas utan å skada meg.',
            'Northern Karelian' => 'Mie voin syvvä lasie ta minla ei ole kipie.',
            'Nórdicg' => 'Ljœr ye caudran créneþ ý jor cẃran.',
            'Odenwälderisch' => 'Iech konn glaasch voschbachteln ohne dass es mir ebbs daun doun dud.',
            'Old French' => 'Je puis mangier del voirre. Ne me nuit.',
            'Old Irish (Latin)' => 'Con·iccim ithi nglano. Ním·géna.',
            'Old Irish (Ogham)' => '᚛᚛ᚉᚑᚅᚔᚉᚉᚔᚋ ᚔᚈᚔ ᚍᚂᚐᚅᚑ ᚅᚔᚋᚌᚓᚅᚐ᚜',
            'Old Norse (Latin)' => 'Ek get etið gler án þess að verða sár.',
            'Old Norse (Runes)' => 'ᛖᚴ ᚷᛖᛏ ᛖᛏᛁ ᚧ ᚷᛚᛖᚱ ᛘᚾ ᚦᛖᛋᛋ ᚨᚧ ᚡᛖ ᚱᚧᚨ ᛋᚨᚱ',
            'Papiamentu' => 'Ami por kome glas anto e no ta hasimi daño.',
            'Pashto' => 'زه شيشه خوړلې شم، هغه ما نه خوږوي',
            'Pfälzisch' => 'Isch konn Glass fresse ohne dasses mer ebbes ausmache dud.',
            'Picard' => 'Ch\'peux mingi du verre, cha m\'foé mie n\'ma.',
            'Polska / Polish' => 'Mogę jeść szkło i mi nie szkodzi.',
            'Provençal / Occitan' => 'Pòdi manjar de veire, me nafrariá pas.',
            'Québécois' => 'J\'peux manger d\'la vitre, ça m\'fa pas mal.',
            'Roman' => 'Me posso magna\' er vetro, e nun me fa male.',
            'Romanian' => 'Pot să mănânc sticlă și ea nu mă rănește.',
            'Romansch (Grischun)' => 'Jau sai mangiar vaider, senza che quai fa donn a mai.',
            'Ruhrdeutsch' => 'Ich kann Glas verkasematuckeln, ohne dattet mich wat jucken tut.',
            'Russian' => 'Я могу есть стекло, оно мне не вредит.',
            'Sami (Northern)' => 'Sáhtán borrat lása, dat ii leat bávččas.',
            'Sanskrit (standard transcription)' => 'kācaṃ śaknomyattum; nopahinasti mām.',
            'Sanskrit' => '﻿काचं शक्नोम्यत्तुम् । नोपहिनस्ति माम् ॥',
            'Schwyzerdütsch (Luzern)' => 'Ech cha Glâs ässe, das schadt mer ned.',
            'Schwyzerdütsch (Zürich)' => 'Ich chan Glaas ässe, das schadt mir nöd.',
            'Schwäbisch / Swabian' => 'I kå Glas frässa, ond des macht mr nix!',
            'Scottish Gaelic' => 'S urrainn dhomh gloinne ithe; cha ghoirtich i mi.',
            'Sicilian' => 'Puotsu mangiari u vitru, nun mi fa mali.',
            'Sinhalese' => 'මට වීදුරු කෑමට හැකියි. එයින් මට කිසි හානියක් සිදු නොවේ.',
            'Slovak' => 'Môžem jesť sklo. Nezraní ma.',
            'Slovenian' => 'Lahko jem steklo, ne da bi mi škodovalo.',
            'Southern Karelian' => 'Minä voin syvvä st\'oklua dai minule ei ole kibie.',
            'Spanish' => 'Puedo comer vidrio, no me hace daño.',
            'Suomi / Finnish' => 'Voin syödä lasia, se ei vahingoita minua.',
            'Svenska / Swedish' => 'Jag kan äta glas utan att skada mig.',
            'Sächsisch / Saxon' => '\'sch kann Glos essn, ohne dass\'sch mer wehtue.',
            'Sønderjysk' => 'Æ ka æe glass uhen at det go mæ naue.',
            'Tagalog' => 'Kaya kong kumain nang bubog at hindi ako masaktan.',
            'Taiwanese' => 'Góa ē-tàng chia̍h po-lê, mā bē tio̍h-siong.',
            'Tamil' => 'நான் கண்ணாடி சாப்பிடுவேன், அதனால் எனக்கு ஒரு கேடும் வராது.',
            'Telugu' => 'నేను గాజు తినగలను మరియు అలా చేసినా నాకు ఏమి ఇబ్బంది లేదు',
            'Thai' => 'ฉันกินกระจกได้ แต่มันไม่ทำให้ฉันเจ็บ',
            'Tibetan' => 'ཤེལ་སྒོ་ཟ་ནས་ང་ན་གི་མ་རེད།',
            'Turkish (Ottoman)' => 'جام ييه بلورم بڭا ضررى طوقونمز',
            'Turkish' => 'Cam yiyebilirim, bana zararı dokunmaz.',
            'Twi' => 'Metumi awe tumpan, ɜnyɜ me hwee.',
            'Ukrainian' => 'Я можу їсти скло, і воно мені не зашкодить.',
            'Ulster Gaelic' => 'Ithim-sa gloine agus ní miste damh é.',
            'Urdu' => 'میں کانچ کھا سکتا ہوں اور مجھے تکلیف نہیں ہوتی ۔',
            'Venetian' => 'Mi posso magnare el vetro, no\'l me fa mae.',
            'Vietnamese (nôm)' => '些 ࣎ 世 咹 水 晶 ও 空 ࣎ 害 咦',
            'Vietnamese (quốc ngữ)' => 'Tôi có thể ăn thủy tinh mà không hại gì.',
            'Walloon' => 'Dji pou magnî do vêre, çoula m\' freut nén må.',
            'Welsh' => 'Dw i\'n gallu bwyta gwydr, \'dyw e ddim yn gwneud dolur i mi.',
            'Yiddish' => 'איך קען עסן גלאָז און עס טוט מיר נישט װײ.',
            'Yoruba' => 'Mo lè je̩ dígí, kò ní pa mí lára.',
            'Zeneise (Genovese)' => 'Pòsso mangiâ o veddro e o no me fà mâ.',
            'Íslenska / Icelandic' => 'Ég get etið gler án þess að meiða mig.',
        ];
    }

    protected function checkFileMediaTypeAndExtensionMethods(): void
    {
        $this->assertInstanceOf(MediaTypeGuesser::class, AbstractFile::getMediaTypeGuesser());
        $this->assertInstanceOf(ExtensionGuesser::class, AbstractFile::getExtensionGuesser());

        $m = new MediaTypeGuesser();
        $e = new ExtensionGuesser();

        $this->assertSame($m, AbstractFile::setMediaTypeGuesser($m));
        $this->assertSame($m, AbstractFile::getMediaTypeGuesser());
        $this->assertSame($e, AbstractFile::setExtensionGuesser($e));
        $this->assertSame($e, AbstractFile::getExtensionGuesser());

        $this->assertNotSame($m, AbstractFile::setMediaTypeGuesser());
        $this->assertNotSame($e, AbstractFile::setExtensionGuesser());

        $this->assertInstanceOf(MediaTypeGuesser::class, AbstractFile::getMediaTypeGuesser());
        $this->assertInstanceOf(ExtensionGuesser::class, AbstractFile::getExtensionGuesser());
    }

    protected function checkFileSizeMethods(FileInterface $file, string $blob): void
    {
        $temp = new FileTemp();
        $temp->acquire();
        $temp->setBlob($blob);
        $size = (new \SplFileInfo($temp->stringifyFile()))->getSize();
        $temp->release();

        $this->assertSame($size, $file->getSizeBytes());
        $this->assertStringMatchesFormat('%s %s', $file->getSizeHuman());
        $this->assertStringMatchesFormat('%s %s%s%s', $file->getSizeHuman(null, true));
        $this->assertStringMatchesFormat('%s.%d%d%d%d%d%d%d%d%d%d %s', $file->getSizeHuman(10));
    }

    protected function checkFileTimeMethods(FileInterface $file, \DateTime $aTime, \DateTime $cTime = null, \DateTime $mTime = null, int $maxDifference = 4): void
    {
        if (null === $cTime) {
            $cTime = $aTime;
        }

        if (null === $mTime) {
            $mTime = $cTime;
        }

        $this->assertDateTimeWithinDifference($maxDifference, $aTime, $file->getAccessedTime());
        $this->assertDateTimeWithinDifference($maxDifference, $cTime, $file->getChangedTime());
        $this->assertDateTimeWithinDifference($maxDifference, $mTime, $file->getModifiedTime());
        $this->assertStringMatchesFormat('%s%s%s %s%s%s %d %d%d%d%d %d:%d%d:%d%d %s', $file->stringifyAccessedTime());
        $this->assertStringMatchesFormat('%s%s%s %s%s%s %d %d%d%d%d %d:%d%d:%d%d %s', $file->stringifyChangedTime());
        $this->assertStringMatchesFormat('%s%s%s %s%s%s %d %d%d%d%d %d:%d%d:%d%d %s', $file->stringifyModifiedTime());

        AbstractFile::setDateTimeFormat('c');
        $this->assertStringMatchesFormat('%d%d%d%d-%d%d-%d%dT%d%d:%d%d:%d%d%s%d%d:%d%d', $file->stringifyAccessedTime());
        $this->assertStringMatchesFormat('%d%d%d%d-%d%d-%d%dT%d%d:%d%d:%d%d%s%d%d:%d%d', $file->stringifyChangedTime());
        $this->assertStringMatchesFormat('%d%d%d%d-%d%d-%d%dT%d%d:%d%d:%d%d%s%d%d:%d%d', $file->stringifyModifiedTime());

        AbstractFile::resetDateTimeFormat();
        $this->assertStringMatchesFormat('%s%s%s %s%s%s %d %d%d%d%d %d:%d%d:%d%d %s', $file->stringifyAccessedTime());
        $this->assertStringMatchesFormat('%s%s%s %s%s%s %d %d%d%d%d %d:%d%d:%d%d %s', $file->stringifyChangedTime());
        $this->assertStringMatchesFormat('%s%s%s %s%s%s %d %d%d%d%d %d:%d%d:%d%d %s', $file->stringifyModifiedTime());
    }

    /**
     * @param string|UuidInterface $uuid
     */
    protected function assertValidUuid($uuid): void
    {
        if ($uuid instanceof UuidInterface) {
            $uuid = $uuid->toString();
        }

        $this->assertSame($uuid, Uuid::fromString($uuid)->toString());
    }

    /**
     * @param string|UuidInterface $uuid
     */
    protected function assertNilUuid($uuid): void
    {
        if ($uuid instanceof UuidInterface) {
            $uuid = $uuid->toString();
        }

        $this->assertSame($uuid, Uuid::fromString($uuid)->toString());
        $this->assertSame(Uuid::NIL, Uuid::fromString($uuid)->toString());
    }

    protected function assertSameDateTime(\DateTime $expected, \DateTime $provided): void
    {
        $this->assertSame($expected->format('U'), $provided->format('U'));
    }

    protected function assertDateTimeWithinDifference(int $allowedDifference, \DateTime $dateTimeOne, \DateTime $dateTimeTwo): void
    {
        $one = (int) $dateTimeOne->format('U');
        $two = (int) $dateTimeTwo->format('U');

        if ($one > $two) {
            $this->assertTrue(($one - $two) <= $allowedDifference);
        } else {
            $this->assertTrue(($two - $one) <= $allowedDifference);
        }
    }

    /**
     * @param string|UuidInterface $uuid
     */
    protected function assertNotValidUuid($uuid): void
    {
        if ($uuid instanceof UuidInterface) {
            $uuid = $uuid->toString();
        }

        $this->assertNotSame($uuid, Uuid::fromString($uuid)->toString());
    }

    protected function createFunctionPath(string $path, int $permissions = 0777, bool $allowExisting = true): string
    {
        $path = sprintf('%s/%s', $this->functionalPath, $path);

        if (false === $allowExisting && file_exists($path)) {
            throw new \RuntimeException(sprintf('Failed creating path: %s (%s)', $path, 'path already exists'));
        }

        self::createDirectoryRecursive($path);

        if (false === $real = realpath($path)) {
            throw new \RuntimeException(sprintf('Failed creating path: %s (%s)', $path, Interpreter::error()->text() ?? 'unknown error'));
        }

        return $real;
    }

    protected function createFunctionFile(string $file, string $path = null, bool $allowExisting = true): string
    {
        $path = sprintf('%s/%s', $this->createFunctionPath($path ?: '/'), $file);

        if (false === $allowExisting && file_exists($path)) {
            throw new \RuntimeException(sprintf('Failed creating file: %s (%s)', $path, 'file already exists'));
        }

        self::createFileRecursive($path);

        if (false === $real = realpath($path)) {
            throw new \RuntimeException(sprintf('Failed creating file: %s (%s)', $path, Interpreter::error()->text() ?? 'unknown error'));
        }

        return $real;
    }

    protected static function createDirectoryRecursive(string $path, int $permissions = 0777): void
    {
        if (!file_exists($path)) {
            if (false === @mkdir($path, $permissions, true)) {
                throw new \RuntimeException(sprintf('Failed creating path: %s (%s)', $path, Interpreter::error()->text() ?? 'unknown error'));
            }
        } else {
            if (false === @chmod($path, $permissions)) {
                throw new \RuntimeException(sprintf('Failed setting %s path permissions: %s (%s)', $permissions, $path, Interpreter::error()->text() ?? 'unknown error'));
            }
        }

        if (!is_writable($path)) {
            throw new \RuntimeException(sprintf('Failed permissions for path: %s (not writable)', $path));
        }
    }

    protected static function createFileRecursive(string $path): void
    {
        if (!file_exists($path)) {
            if (false === @touch($path)) {
                throw new \RuntimeException(sprintf('Failed creating file: %s (%s)', $path, Interpreter::error()->text() ?? 'unknown error'));
            }
        }

        if (!is_writable($path)) {
            throw new \RuntimeException(sprintf('Failed permissions for file: %s (not writable)', $path));
        }
    }

    protected static function removeDirectoryRecursive(string $path): void
    {
        if (is_dir($path)) {
            foreach (new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS) as $child) {
                self::removeDirectoryRecursive($child);
            }

            if (false === @rmdir($path)) {
                throw new \RuntimeException(sprintf('Failed removing path: %s (%s)', $path, Interpreter::error()->text() ?? 'unknown error'));
            }
        } else {
            self::removeFile($path);
        }
    }

    protected static function removeFile(string $file): void
    {
        if (false === @unlink($file)) {
            throw new \RuntimeException(sprintf('Failed removing file: %s (%s)', $file, Interpreter::error()->text() ?? 'unknown error'));
        }
    }
}
