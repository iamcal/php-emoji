2023-11-07
================================

## emoji 轉換成 html span 標籤 (PDF 建立用)

1. 執行建立對應資料 [emoji-data](emoji-data) 可放入[指定版本 emoji 檔案](https://github.com/iamcal/emoji-data)
   ，執行 `./build.sh`
2. lib 資料夾產生檔案
    ```
    ../emoji.png
    ../emoji.php
    ../emoji.css
    ../emoji-map.json
    ```
3. [emoji.php](..%2Flib%2Femoji.php) 比對相對應的 emoji 轉換陣列改成匯入 json ，建立對應資料時會自動生成。
4. emoji 轉換成 html span 標籤方法
   ```php
   $data = emoji_unified_to_html($data);
   ```

Just looking for the library?
=============================

You don't need to worry about the contents of this directory - just use `emoji.php`,
`emoji.css` and `emoji.png` in the parent directory.


I'm a developer, tell me more...
================================

The scripts in this directory allow you to build the library from the emoji-data source material.

The emoji-data repo contains a list of emoji with supporting images (and spritesheets).

We use this data to build the PHP map and the CSS file:

    ./build.sh

The following files are created by this process:

    ../emoji.png
    ../emoji.php
    ../emoji.css
    ../table.htm
