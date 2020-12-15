<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Config;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use ISAAC\CodeSnifferBaseliner\Baseline\Baseline;
use LogicException;
use RuntimeException;

use function count;
use function in_array;

class Config
{
    /**
     * @var DOMDocument
     */
    private $document;

    public function __construct(DOMDocument $document)
    {
        $this->document = $document;
    }

    public function getFiles(): iterable
    {
        $files = (new DOMXPath($this->document))->query('/ruleset/file');
        if ($files === false) {
            throw new LogicException('XPath query failed.');
        }
        foreach ($files as $file) {
            if (!$file instanceof DOMElement) {
                continue;
            }
            yield trim($file->textContent);
        }
    }

    public function mergeBaseline(Baseline $baseline): void
    {
        if (count($this->document->childNodes) === 0) {
            throw new RuntimeException('Config file has no root element.');
        }
        $rootNode = $this->document->childNodes[0];
        $ruleConfigs = (new DOMXPath($this->document))->query('/ruleset/rule');
        if ($ruleConfigs === false) {
            throw new LogicException('XPath query failed.');
        }
        $rulesConfigured = [];
        foreach ($ruleConfigs as $ruleConfig) {
            if (!$ruleConfig instanceof DOMElement) {
                continue;
            }
            $ruleName = (new DOMXPath($this->document))->evaluate('string(./@ref)', $ruleConfig);
            $this->addFileExclusionsToRuleConfig($ruleConfig, $baseline->getFilesForRule($ruleName));
            $rulesConfigured[] = $ruleName;
        }
        foreach ($baseline->getViolatedRulesByFileAndLineNumber() as $ruleName => $filenames) {
            if (in_array($ruleName, $rulesConfigured, true)) {
                continue;
            }
            $this->createRuleConfig($rootNode, $ruleName, $filenames);
            $rulesConfigured[] = $ruleName;
        }
    }

    /**
     * @param string[] $filenames
     */
    private function createRuleConfig(
        DOMElement $rootNode,
        string $ruleName,
        array $filenames
    ): void {
        $ruleConfig = $this->document->createElement('rule');
        $ruleConfig->setAttribute('ref', $ruleName);
        $rootNode->appendChild($ruleConfig);
        $this->addFileExclusionsToRuleConfig($ruleConfig, $filenames);
    }

    /**
     * @param string[] $filenames
     */
    private function addFileExclusionsToRuleConfig(DOMNode $ruleConfig, array $filenames): void
    {
        foreach ($filenames as $filename) {
            $excludePattern = $this->document->createElement('exclude-pattern', $filename);
            $excludePattern->setAttribute('baseline', 'baseline');
            $ruleConfig->appendChild($excludePattern);
        }
    }

    public function removeBaseline(): void
    {
        foreach ($this->getBaselineExclusions() as $baselineExclusion) {
            $this->removeExclusion($baselineExclusion);
        }
    }

    public function removeBaselineExclusionsNotInBaseline(Baseline $baseline): void
    {
        foreach ($this->getBaselineExclusions() as $baselineExclusion) {
            if ($baselineExclusion->parentNode === null) {
                throw new LogicException('Baseline exclusion has no parent node.');
            }
            $ruleName = (new DOMXPath($this->document))->evaluate('string(./@ref)', $baselineExclusion->parentNode);
            if (!$baseline->containsExclusion($ruleName, $baselineExclusion->textContent)) {
                $this->removeExclusion($baselineExclusion);
            }
        }
    }

    /**
     * @return DOMNodeList<DOMNode>
     */
    private function getBaselineExclusions(): DOMNodeList
    {
        $baselineExclusions = (new DOMXPath($this->document))->query('/ruleset/rule/exclude-pattern[@baseline]');
        if ($baselineExclusions === false) {
            throw new LogicException('XPath query failed.');
        }
        return $baselineExclusions;
    }

    private function removeExclusion(DOMNode $exclusion): void
    {
        $ruleConfig = $exclusion->parentNode;
        if ($ruleConfig === null) {
            throw new LogicException('Baseline exclusion has no parent node.');
        }
        $ruleConfig->removeChild($exclusion);
        if (count($ruleConfig->childNodes) === 0) {
            if ($ruleConfig->parentNode === null) {
                throw new LogicException('Rule configuration has no parent node.');
            }
            $ruleConfig->parentNode->removeChild($ruleConfig);
        }
    }

    public function toXml(): string
    {
        return $this->document->saveXML();
    }

    public function __clone()
    {
        $this->document = clone $this->document;
    }
}
