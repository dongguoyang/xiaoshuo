<div class="array-blade" style="height: 50px;overflow: hidden;border: 1px #ddd solid;border-radius: 3px;padding: 5px;cursor: pointer;" title="点我展开全部">
    <?php 
        if (!is_array($value)) {
            if (IsJson($value)) {
                $value = json_decode($value, 1);
            } else {
                $value = ExplodeStr($value);
            }
        }

        $str = "{<br>";
        foreach ($value as $k => $v) {
            if ($v === '') {
                continue;
            }
            $str .= '<span style="display:inline-block;width:20px;"></span>' . $k . ' => ' . $v . "<br>";
        }
        $str .= "}";
        echo $str;
    ?>
</div>