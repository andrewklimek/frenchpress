<?php
/**
 * This is my settings page framework
 * Should be included with variables $options, $endpoint, $title (optional)
 */

if ( empty( $options ) || empty( $endpoint ) ) return;

echo "<div class=wrap>";
if (!empty($title)) echo "<h1>$title</h1>";
// if (!empty($_GET['updated'])) echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';

echo '<form method=post id=mnml-settings-form>';
echo '<table class=form-table>';
foreach ($options as $group => $fields) {
    $serial = substr($group, -1) !== '_';
    if ($serial) {
        $values = get_option($group);
    }
    foreach ($fields as $k => $f) {
        $l = isset($f['label']) ? $f['label'] : str_replace('_', ' ', $k);

        if ($serial) {
            $v = $values[$k] ?? $f['default'] ?? '';
            $name = "opts[{$group}][{$k}]";
            $k = "{$group}_{$k}";
        } else {
            $k = $group . $k;
            $name = "opts[{$k}]";
            $v = get_option($k, $f['default'] ?? '');
        }

        if (!empty($f['before'])) echo "<tr><th>" . $f['before'];
        $ph = !empty($f['placeholder']) ? $f['placeholder'] : '';
        $size = !empty($f['size']) ? $f['size'] : 'regular';
        $hide = '';
        $script = '';
        if (!empty($f['show'])) {
            if (is_string($f['show'])) $f['show'] = [$f['show'] => 'any'];
            foreach ($f['show'] as $target => $cond) {
                if ($serial) {
                    $target_value = $values[$target] ?? null;
                    $target = "{$group}_{$target}";
                } else {
                    $target = $group . $target;
                    $target_value = get_option($target, null);
                }

                $hide = " style='display:none'";
                $script .= "\ndocument.querySelector('#tr-{$target}').addEventListener('change', function(e){";
                if ($cond === 'any') {
                    $script .= "if( e.target.checked !== false && e.target.value )";
                    if ($target_value) $hide = "";
                } elseif ($cond === 'empty') {
                    $script .= "if( e.target.checked === false || !e.target.value )";
                    if (!$target_value) $hide = "";
                } else {
                    $script .= "if( !!~['". implode("','", (array)$cond)."'].indexOf(e.target.value) && e.target.checked!==false)";
                    if ($target_value && in_array($target_value, (array)$cond)) $hide = "";
                }
                $script .= "{document.querySelector('#tr-{$k}').style.display='revert'}";
                $script .= "else{document.querySelector('#tr-{$k}').style.display='none'}";
                $script .= "});";
            }
        }
        if (empty($f['type'])) $f['type'] = !empty($f['options']) ? 'radio' : 'checkbox';

        if ($f['type'] === 'section') { echo "<tbody id='tr-{$k}' {$hide}>"; continue; }
        elseif ($f['type'] === 'section_end') { echo "</tbody>"; continue; }
        else echo "<tr id=tr-{$k} {$hide}><th>";

        if (!empty($f['callback']) && is_callable($f['callback']) ) {
            echo "<label for='{$k}'>{$l}</label><td>";
            call_user_func($f['callback'], $k, $v, $f);
        } else {
            switch ($f['type']) {
                case 'textarea':
                    echo "<label for='{$k}'>{$l}</label><td><textarea id='{$k}' name='{$name}' placeholder='{$ph}' rows=8 class={$size}-text>" . esc_textarea($v) . "</textarea>";
                    break;
                case 'code':
                    echo "<label for='{$k}'>{$l}</label><td><textarea id='{$k}' name='{$name}' placeholder='{$ph}' rows=8 class='large-text code'>" . esc_textarea($v) . "</textarea>";
                    break;
                case 'number':
                    echo "<label for='{$k}'>{$l}</label><td><input id='{$k}' name='{$name}' placeholder='{$ph}' value='" . esc_attr($v) . "' class={$size}-text type=number>";
                    break;
                case 'radio':
                    if (!empty($f['options']) && is_array($f['options'])) {
                        echo "{$l}<td>";
                        foreach ($f['options'] as $ov => $ol) {
                            if (!is_string($ov)) $ov = $ol;
                            echo "<label><input name='{$name}' value='" . esc_attr($ov) . "'"; if ($v == $ov) echo " checked"; echo " type=radio>" . esc_html($ol) . "</label> ";
                        }
                    }
                    break;
                case 'select':
                    if (!empty($f['options']) && is_array($f['options'])) {
                        echo "<label for='{$k}'>{$l}</label><td><select id='{$k}' name='{$name}'>";
                        echo "<option value=''></option>";
                        foreach ($f['options'] as $key => $value) {
                            echo "<option value='" . esc_attr($key) . "'" . selected($v, $key, false) . ">" . esc_html($value) . "</option>";
                        }
                        echo "</select>";
                    }
                    break;
                case 'text':
                    echo "<label for='{$k}'>{$l}</label><td><input id='{$k}' name='{$name}' placeholder='{$ph}' value='" . esc_attr($v) . "' class={$size}-text>";
                    break;
                case 'checkbox':
                    echo "<input type=hidden name='{$name}' value=''>";
                    echo "<label for='{$k}'>{$l}</label><td><input id='{$k}' name='{$name}'"; if ($v) echo " checked"; echo " type=checkbox>";
                    break;
                default:
                    echo "<label for='{$k}'>{$l}</label><td><input id='{$k}' name='{$name}' placeholder='{$ph}' value='" . esc_attr($v) . "' class={$size}-text type='" . esc_attr($f['type']) . "'>";
                    break;
            }
        }
        if (!empty($f['desc'])) echo " " . esc_html($f['desc']);
    }
}
?>
<script>
document.getElementById('mnml-settings-form').addEventListener('change', function(e) {
  const form = e.target.form;
  const button = form.querySelector('.button-primary');
  if (!form || !button) return;

  button.disabled = true;
  button.innerText = 'Saving...';

  clearTimeout(window.fpSaveTimeout);
  window.fpSaveTimeout = setTimeout(() => {
    fetch('<?php echo esc_js($endpoint); ?>', {
      method: 'POST',
      headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' },
      body: new FormData(form)
    })
    .then(r => r.text())
    .then(resp => {
      button.innerText = 'Saved';
      button.disabled = false;

      document.dispatchEvent(new CustomEvent('mnmlsettings:saved', {
        detail: { response: resp, savedAt: Date.now() }
      }));

	  setTimeout(() => { button.innerText = 'Save Changes'; }, 2000);
    })
    .catch(err => {
      button.innerText = 'Error – retry';
      button.disabled = false;
      console.error('FrenchPress save failed:', err);
    });
  }, 800);
});
<?php 
echo $script;
echo "</script>";
echo "</table>";
echo "<div style='position:fixed;bottom:0;left:0;right:0;padding:16px 0 16px 180px;z-index:1;background:#1d2327'><button class=button-primary>Save Changes</button></div>";
echo "</form></div>";