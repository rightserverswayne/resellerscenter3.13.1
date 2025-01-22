<?php
use MGModule\ResellersCenter\Core\Helpers\Urls\Url;

return
[
    Url::KNOWLEDGEBASE =>
    [
        "type"      => Url::KNOWLEDGEBASE,
        "regexp"    =>
        [
            "basic"             =>
            [
                "url"    => "(?<!(index\.php\/))knowledgebase\.php?", //https://multibug.local/knowledgebase.php?action=displayarticle&id=1
                "params" => "(?<=(\?|&)).*?(?=&|$|\s)",
            ],
            "rewrite"           =>
            [
                "url"    => "(?<!(index\.php\/))knowledgebase", //https://multibug.local/knowledgebase/1/SMTP.html
                "params" => "(?<=\/knowledgebase\/).*?(?=\/)",
            ],
            "acceptpathinfo"    =>
            [
                "url"    => "index.php\/knowledgebase\.php?", //https://multibug.local/index.php/knowledgebase.php?action=displayarticle&id=1
                "params" => "(?<=(\?|&)).*?(?=&|$|\s)",
            ],
        ]
    ],

    Url::ANNOUNCEMENTS =>
    [
        "type"      => Url::ANNOUNCEMENTS,
        "regexp"    =>
        [
            "basic"             =>
            [
                "url"    => "(?<!(index\.php\/))announcements\.php?",
                "params" => "(?<=(\?|&)).*?(?=&|$|\s)",
            ],
            "rewrite"           =>
            [
                "url"    => "(?<!(index.php))announcements",
                "params" => "(?<=\/announcements\/).*?(?=\/)",
            ],
            "acceptpathinfo"    =>
            [
                "url"    => "index.php\/announcements\.php?",
                "params" => "(?<=(\?|&)).*?(?=&|$|\s)",
            ],
        ]
    ],

    Url::CART =>
    [
        "type"      => Url::CART,
        "regexp"    =>
        [
            "basic"             =>
            [
                "url"    => "(?<=(rp=\/))cart\/",
                "params" => "(?<=(\?|&)).*?(?=&|$|\s)",
            ],
            "rewrite"           =>
            [
                "url"    => "((?<!(index\.php\/))(?<!(rp=\/)))cart",
                //                        "params" => "(?<=/cart/).*?(?=/)",
            ],
            "acceptpathinfo"    =>
            [
                "url"    => "index.php\/cart",
                "params" => "(?<=(\?|&)).*?(?=&|$|\s)",
            ],
            ]
    ],
];