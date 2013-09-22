<?php

class Generator
{
    private $lists = array();

    private function getNamesList()
    {
        return $this->unpackList('names', "
            Иван|Антон|anon|x3kep|аноним|me|Иоанн V|анонимус|анониммс|no name no face no address
            noone|stranger|starkid|римлянин
        ");
    }

    private function getProducts()
    {
        return $this->unpackList('products', "
            PHP|Windows|MySQL|Node.js|Python|Ruby|JS
            Wordpress|MacOS X|iOS|Drupal|ZF1|ZF2|Symfony|Yii|Visual Basic
        ");
    }

    private function getHeadersList()
    {
        return array(
            array('Проблемы', 'Особенности', 'Преимущества', 'Недостатки', 
                'Достоинства', 'Сложности', 'Новости', 'Заказ'),
            array('скриптов на', 'разработки под', 'работы с', 'доработки', 
                'переделки', 'отладки', 'и особенности', 'скриптов', 'программ', 
                'разработчиков', 'систем')
        );
    }

    private function getThemesList()
    {
        $list = "
        Вопросы по|Тред|Все о|Учим|Не могу понять|Не могу разобраться с
        Не понимаю|Задай вопрос по|Что такое|Кто работал с|Как использовать
        Как писать под|Кот использует|Кто знает|Кому нужен|А вам не нужен|Haskell vs
        Ruby и|Тред для|Тред|Обсуждение|Вопросы о|Need help with|Tell me about
        Место|Поговорим о|Разговоры о|Тема:|Расскажите о|Зачем нужен
        Я использую|Нужен ли|Используют ли|Кто юзал|Друг подсадил на
        Кто разобрался в|Что делать с|Как использовать|Все о|FAQ по|FAQ|Дискач
        Мой кот выучил|Кот установил
        ";

        return $this->unpackList('themes', $list);
    }

    private function getEmojiList()
    {
        return $this->unpackList('emoji', "
            ^ ^|:)|xD|:-)|)))|((|:3|`_́|=]|8D|(>_<)|(¬_¬)ﾉ
            (」゜ロ゜)」|(/ﾟДﾟ)/|(；￣Д￣）|(¬_¬)| ＼(｀0´)／|-`д´-
            ＜(。_。)＞|(｡･ω･｡)|(=｀ω´=)|ヾ(=ﾟ･ﾟ=)ﾉ|(´･_･`)|
            (」ﾟヘﾟ)」| ٩(͡๏̯͡๏)۶|(●__●)|。(⌒∇⌒。)|☆| (°◇°;)|
            ))))|lol|^ ^|:3|｡◕‿◕｡|（⌒▽⌒）|:)|(((|:(|☆彡|☂|*・°☆.。|～
            (☞ﾟ∀ﾟ)☞|(个_个)ヽ(ﾟДﾟ)ﾉ
        ");
    }

    private function getShortComments()
    {
        if (empty($this->shortComments)) {
            $comments = file(__DIR__ . '/comments.txt');
            $comments = array_filter(array_map('trim', $comments));
            $this->shortComments = $comments;
        }

        return $this->shortComments;
    }

    private function unpackList($key, $str)
    {
        if (empty($this->lists[$key])) {
            $list = array_map('trim', preg_split("![|\\n]!", $str));
            $list = array_filter($list);
            $this->lists[$key] = $list;
        }

        return $this->lists[$key];
    }

    private function loadWordList($key, $filename)
    {
        if (empty($this->lists[$key])) {
            $source = file_get_contents(__DIR__ . '/' . $filename);
            if (false === $source) {
                throw new Exception("Failed to read file $filename");                
            }

            $linkRe = "!([a-z]:|https?://|ftp://)[^\\s'\"а-яA-ЯёЁ*(){}]{3,}!ui";
            preg_match_all($linkRe, $source, $linkSet);
            $source = preg_replace($linkRe, '', $source);

            $words = preg_split("/[\\s.,?!:;()'\"\\-]+/", $source);

            // add links
            $words = array_merge($words, $linkSet[0]);

            $words = array_map('trim', $words);
            $words = array_filter($words);

            $this->lists[$key] = $words;
        }

        return $this->lists[$key];
    }

    private function selectOne(array $options)
    {
        return $options[array_rand($options)];
    }

    private function selectSeveral(array $options, $count)
    {
        $from = mt_rand(0, count($options) - $count);
        $items = array_slice($options, $from, $count);
        return $items;
    }

    private function selectDistributed(array $options)
    {
        $sum = array_sum($options);
        $random = mt_rand(0, $sum);

        foreach ($options as $option => $probability) {
            if ($random < $probability) {
                return $option;
            }

            $random -= $probability;
        }

        return $option;
    }


    private function getRandomProduct()
    {
        return $this->selectOne($this->getProducts());
    }

    private function getRandomProductSet()
    {
        $result = $this->getRandomProduct();
        $r = mt_rand(1, 100);
        if ($r > 94) {
            $result .= '/' . $this->getRandomProduct();
        } else if ($r > 86) {
            $result .= ' и ' . $this->getRandomProduct();
        } else if ($r > 78) {
            $result .= ' или ' . $this->getRandomProduct();
        }

        return $result;
    }

    private function getRandomEmoji()
    {
        return $this->selectOne($this->getEmojiList());
    }

    private function getWordList()
    {
        return $this->loadWordList('words', 'words.txt');
    }

    private function generateTopic()
    {
        $type = mt_rand(1, 20);
        if ($type > 16) {
            $headers = $this->getHeadersList();
            $start = $this->selectOne($headers[0]). ' ' . 
                     $this->selectOne($headers[1]);

        } else if ($type > 15) {
            $result = "{$this->getRandomProductSet()} или {$this->getRandomProductSet()}?";
            return $result;
        } else {
            $start = $this->selectOne($this->getThemesList());
        }

        $products = $this->getRandomProductSet();
        $result = "$start $products";
        return $result;
    }

    private function getRandomName()
    {
        return $this->selectOne($this->getNamesList());
    }    

    private function ucFirst($text)
    {
        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    private function cropShort(array $words, $minLength = 3)
    {
        while (count($words)) {
            if (mb_strlen(end($words)) >= $minLength) {
                break;
            }

            array_pop($words);
        }

        return $words;
    }

    private function generatePhrase(array $words, $wordsCount, $minWords = 2)
    {
        $sentenceWords = array();
        while (count($sentenceWords) < $minWords) {
            $sentenceWords = array_merge($sentenceWords, $this->selectSeveral($words, $wordsCount));
            $sentenceWords = $this->cropShort($sentenceWords);
        }

        return implode(' ', $sentenceWords);
    }

    private function generateText($minSentences = 1, $maxSentences = 20, $maxWords = 12)
    {
        $sentences = mt_rand($minSentences, $maxSentences);

        $signs = array('.' => 70, ',' => 5, '?' => '5', '!' => 5, 
            '!!' => 1, '...' => 2, ':' => 4, ';' => 4, '??' => 2);
        $endingSigns = array('.' => 70, '?' => 15, '!' => 5, '...' => 4);

        $words = $this->getWordList();
        $result = '';
        $prevSign = '';

        for ($i = 0; $i < $sentences; $i++) {
            $sign = $this->selectDistributed($i < ($sentences -1) ? $signs : $endingSigns);
            $length = mt_rand(2, $maxWords);
            $sentence = $this->generatePhrase($words, $length);
            $needUppercase = in_array($prevSign, array('', '.', '?', '!', '...', '!!', '??'));

            if ($needUppercase) {
                if (mt_rand(1, 100) < 20) {
                    $result .= "\n\n";
                }

                $sentence = $this->ucFirst($sentence);
            }

            $result .= $sentence . $sign . ' ';
            $prevSign = $sign;

            if (mt_rand(1, 100) < 8) {
                $result .= " {$this->getRandomEmoji()} ";
            }            
        }

        $result = trim($result);
        return $result;
    }

    private function getRandomShortComment()
    {
        $comment = $this->selectOne($this->getShortComments());
        $comment = $this->replacePlaceholders($comment);

        return $comment;
    }

    private function replacePlaceholders($comment) {

        $that = $this;
        $comment = preg_replace_callback("/%([a-zA-Z]+)%/", function ($m) use ($that) {

            $type = $m[1];
            if ($type == 'product') {
                return $that->getRandomProduct();
            } elseif ($type == 'productset') {
                return $that->getRandomProduct();
            } elseif ($type == 'name') {
                return $that->getRandomName();
            } elseif ($type == 'emoji') {
                return $that->getRandomEmoji();
            } elseif ($type == 'phrase') {
                return $that->generatePhrase($this->getWordList(), mt_rand(1, 12));
            }

            return $m[0];

        }, $comment);

        return $comment;
    }

    public function generateCommentText()
    {
        $type = mt_rand(1, 100);        

        if ($type < 5) {
            $comment = $this->generateText(3, 12);
        } else if ($type < 30) {
            $comment = $this->generateText(1, 6, 5);
        } else {
            $comment = $this->getRandomShortComment();
            if (mt_rand(1, 100) < 14) {
                $comment .= ' ' . $this->getRandomEmoji();
            }
            
            if (mt_rand(1, 100) < 15) {
                $comment .= "\n\n" . $this->generateText(1, 6, 8);
            }
        }

        return $comment;
    }

    public function test()
    {
        echo "Topic: {$this->generateTopic()}\n";
        echo "Name: {$this->getRandomName()}\n";
        echo "Text: {$this->generateText()}\n";

        for ($i = 0; $i < 100; $i++) {
            $comment = $this->generateCommentText();
            echo "\n--\n" . $comment . "\n\n";
        }
    }

    public function generatePost()
    {
        $post = new core_Post();
        $post->title = '';
        $post->content = $this->generateText();

        if (mt_rand(1, 100) < 60) {
            $post->title = $this->generateTopic();
        }

        return $post;        
    }

    public function generateComment()
    {
        $comment = new core_Comment();
        $comment->content = $this->generateCommentText();

        return $comment;
    }
}