<?php
$real_path = 'test.docx';
$zip = new ZipArchive;
$indentation_unit = '    ';
$indentation_array = [];
$content = '';
// Open received archive file
if (true === $zip->open($real_path)) {
    // If done, search for the data file in the archive
    if (($index = $zip->locateName('word/document.xml')) !== false) {
        // If found, read it to the string
        $data = $zip->getFromIndex($index);
        echo 'data: '.$data."\n";
        // Close archive file
        $zip->close();
        // Load XML from a string
        // Skip errors and warnings
        $xml = new DOMDocument();
        $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        $elements = $xml->documentElement->firstChild->childNodes;
        $total_element = $elements->count();
        echo "\n---------\ntotal_element: $total_element\n";

        for ($i = 0; $i < $total_element; $i++) {
            echo "\n---------\ntext: ".$elements->item($i)->nodeName."  = ".$elements->item($i)->nodeValue."\n";
            if($elements->item($i)->nodeName != 'w:p') {
                continue;
            }
            //var_dump($elements->item($i)->textContent);
            $line = '';
            $line_parent = $elements->item($i)->childNodes;
            $child_count = $line_parent->count();
            echo "*****\n";
            for($j = 0; $j < $child_count; $j++) {
                $line_child = $line_parent->item($j);
                $inline_node_name = $line_child->nodeName;
                // 不严格检查行内具体格式，只关注开头/结尾  注意：一下顺序不能调整，必须先开头，最后结尾
                // 检查开头空格
                if($inline_node_name == 'w:pPr') {
                    $sub_children = $line_child->childNodes;
                    $sub_children_count = $sub_children->count();
                    for($m = 0; $m < $sub_children_count; $m++) {
                        $sub_child = $sub_children->item($m);
                        $sub_child_node_name = $sub_child->nodeName;
                        if($sub_child_node_name == 'w:ind') {
                            $ind_attributes = $sub_child->attributes;
                            $indentation = getIndentation($ind_attributes);
                            $line .= getIndentationTag($indentation);// 空行标记，最后替换
                        }
                    }
                }
                $line .= $line_child->nodeValue;// 不保证空，所以保证顺序
                // 行内换行
                if($inline_node_name == 'w:r') {
                    $sub_children = $line_child->childNodes;
                    $sub_children_count = $sub_children->count();
                    for($m = 0; $m < $sub_children_count; $m++) {
                        $sub_child = $sub_children->item($m);
                        $sub_child_node_name = $sub_child->nodeName;
                        if($sub_child_node_name == 'w:br') {
                            $line .= "\n";// 换行
                        }
                    }
                }
            }
            $line .= "\n";// 行结束
            $content .= $line;
            echo $line;
            echo "*****\n";
            //file_put_contents('test.log', "\n---------\ntext: ".$elements->item($i)->nodeName."  = ".$elements->item($i)->nodeValue."\r\n", FILE_APPEND);
        }
        // 组装
        if($indentation_array) {
            ksort($indentation_array);
            $find = [];
            $replace = [];
            $offset = 0;
            foreach($indentation_array as $index => $ind) {
                $find[] = getIndentationTag($index);
                $replace[] = str_repeat($indentation_unit, ++$offset);
            }
            $content = str_replace($find, $replace, $content);
        }
        print_r($indentation_array);
        echo "[content]: ".$content."\n";
    } else {
        $zip->close();
    }
} else {
    echo "fail to open file\n";
}

function createNewLine($dom_element) {}

function getIndentation($ind_attributes) {
    global $indentation_array;
    foreach($ind_attributes as $ind_attribute_name => $ind_attribute) {
        echo "<<attribute: $ind_attribute_name = {$ind_attribute->value}>>\n";
        if(stripos('w:firstLineChars', $ind_attribute_name) !== false) {
            $indentation_array[$ind_attribute->value] = '    ';
            return $ind_attribute->value;
        }
    }
    return 0;
}

function getIndentationTag($size) {
    return '[[indentation'.$size.']]';
}