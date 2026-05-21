<?php
$jsonlFile = __DIR__ . '/../Radiif_Master_16-5-2026.jsonl';
$handle = fopen($jsonlFile, 'r');
$found = false;

while (($line = fgets($handle)) !== false) {
    $data = json_decode($line, true);
    if (isset($data['qa_pairs'])) {
        foreach ($data['qa_pairs'] as $pair) {
            if (trim($pair['question']) === 'متى يحق للمدعي المطالبة بأتعاب المحاماة في الدعاوى التجارية؟') {
                $found = true;
                echo "Question found!\n";
                echo "QA Pair details:\n";
                print_r($pair);
                echo "Case Metadata:\n";
                print_r($data['metadata'] ?? []);
                break 2;
            }
        }
    }
}

if (!$found) {
    echo "Question not found by exact string, let's try a partial search.\n";
    rewind($handle);
    while (($line = fgets($handle)) !== false) {
        $data = json_decode($line, true);
        if (isset($data['qa_pairs'])) {
            foreach ($data['qa_pairs'] as $pair) {
                if (str_contains($pair['question'], 'أتعاب المحاماة')) {
                    echo "Partial Match found:\n";
                    echo "Q: " . $pair['question'] . "\n";
                    print_r($pair['legal_articles'] ?? []);
                    echo "---\n";
                }
            }
        }
    }
}
fclose($handle);
