<?php

namespace duan617\sensitive\dfa;

class SensitiveDfa
{

    /**
     * 待检测词汇长度
     *
     * @var int
     */
    protected $contentLength = 0;

    /**
     * 敏感词单例
     *
     * @var object|null
     */
    private static $_instance = null;

    /**
     * 敏感词库树
     *
     * @var HashMap|null
     */
    protected $wordTree = null;

    /**
     * 存放待检测语句敏感词
     *
     * @var array|null
     */
    protected static $badWordList = null;

    /**
     * 获取单例
     *
     * @return self
     */
    public static function init()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 构建敏感词树-文件模式
     * @param string $filepath
     * @return $this
     * @throws \Exception
     */
    public function setTreeByFile($filepath = '')
    {
        if (!file_exists($filepath)) {
            throw new \Exception('词库文件不存在');
        }

        $this->wordTree = new HashMap();

        foreach ($this->yieldToReadFile($filepath) as $word) {
            $this->buildWordToTree(trim($word));
        }

        return $this;
    }

    /**
     * 构建敏感词树-数组模式
     * @param null $sensitiveWords
     * @return $this
     * @throws \Exception
     */
    public function setTree($sensitiveWords = null)
    {
        if (empty($sensitiveWords)) {
            throw new \Exception('词库不能为空');
        }

        $this->wordTree = new HashMap();

        foreach ($sensitiveWords as $word) {
            $this->buildWordToTree($word);
        }
        return $this;
    }

    /**
     * 检测文字中的敏感词
     * @param string $content 待检测内容
     * @param int $matchType 匹配类型 [默认为最小匹配规则]
     * @param int $wordNum 需要获取的敏感词数量 [默认获取全部]
     * @return array
     */
    public function getBadWord($content, $matchType = 1, $wordNum = 0)
    {
        $this->contentLength = mb_strlen($content, 'utf-8');
        $badWordList = array();
        for ($length = 0; $length < $this->contentLength; $length++) {
            $matchFlag = 0;
            $flag = false;
            $tempMap = $this->wordTree;
            for ($i = $length; $i < $this->contentLength; $i++) {
                $keyChar = mb_substr($content, $i, 1, 'utf-8');

                $nowMap = $tempMap->get($keyChar);

                if (empty($nowMap)) {
                    break;
                }

                $tempMap = $nowMap;
                $matchFlag++;

                if (false === $nowMap->get('ending')) {
                    continue;
                }

                $flag = true;

                if (1 === $matchType) {
                    break;
                }
            }

            if (!$flag) {
                $matchFlag = 0;
            }

            if ($matchFlag <= 0) {
                continue;
            }

            $badWordList[] = mb_substr($content, $length, $matchFlag, 'utf-8');

            if ($wordNum > 0 && count($badWordList) == $wordNum) {
                return $badWordList;
            }

            $length = $length + $matchFlag - 1;
        }
        return $badWordList;
    }

    /**
     * 替换敏感字字符
     * @param $content
     * @param string $replaceChar
     * @param string $sTag
     * @param string $eTag
     * @param int $matchType
     * @return mixed
     * @throws \Exception
     */
    public function replace($content, $replaceChar = '', $sTag = '', $eTag = '', $matchType = 1)
    {
        if (mb_strlen(trim($content))) {
            throw new \Exception('请填写检测的内容');
        }

        if (empty(self::$badWordList)) {
            $badWordList = $this->getBadWord($content, $matchType);
        } else {
            $badWordList = self::$badWordList;
        }

        if (empty($badWordList)) {
            return $content;
        }

        foreach ($badWordList as $badWord) {
            if ($sTag || $eTag) {
                $replaceChar = $sTag . $badWord . $eTag;
            }
            $content = str_replace($badWord, $replaceChar, $content);
        }
        return $content;
    }

    /**
     * 被检测内容是否合法
     * @param $content
     * @return bool
     */
    public function islegal($content)
    {
        $this->contentLength = mb_strlen($content, 'utf-8');

        for ($length = 0; $length < $this->contentLength; $length++) {
            $matchFlag = 0;

            $tempMap = $this->wordTree;
            for ($i = $length; $i < $this->contentLength; $i++) {
                $keyChar = mb_substr($content, $i, 1, 'utf-8');

                $nowMap = $tempMap->get($keyChar);

                if (empty($nowMap)) {
                    break;
                }

                $tempMap = $nowMap;
                $matchFlag++;

                if (false === $nowMap->get('ending')) {
                    continue;
                }

                return true;
            }

            if ($matchFlag <= 0) {
                continue;
            }

            $length = $length + $matchFlag - 1;
        }
        return false;
    }

    /**
     * 迭代式读取文件
     * @param $filepath
     * @return \Generator
     */
    protected function yieldToReadFile($filepath)
    {
        $fp = fopen($filepath, 'r');
        while (!feof($fp)) {
            yield fgets($fp);
        }
        fclose($fp);
    }

    /**
     * 将单个敏感词构建成树结构
     * @param string $word
     */
    protected function buildWordToTree($word = '')
    {
        if ('' === $word) {
            return;
        }
        $tree = $this->wordTree;

        $wordLength = mb_strlen($word, 'utf-8');
        for ($i = 0; $i < $wordLength; $i++) {
            $keyChar = mb_substr($word, $i, 1, 'utf-8');

            $tempTree = $tree->get($keyChar);

            if ($tempTree) {
                $tree = $tempTree;
            } else {
                $newTree = new HashMap();
                $newTree->put('ending', false);

                $tree->put($keyChar, $newTree);
                $tree = $newTree;
            }

            if ($i == $wordLength - 1) {
                $tree->put('ending', true);
            }
        }
        return;
    }

}