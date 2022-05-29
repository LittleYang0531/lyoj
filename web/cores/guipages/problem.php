<?php
function run(array $param,string &$html,string &$body):void {
    $tmp=""; if (array_key_exists("id",$param)) {
        info_run($param,$html,$body);
        return;
    } if (!array_key_exists("key",$param)) $_GET["key"]="";
    if (!array_key_exists("tag",$param)) $_GET["tag"]="";
    if (!array_key_exists("diff",$param)) $_GET["diff"]="";
    $t=explode(",",$_GET["tag"]);array_splice($t,count($t)-1,1);
    $d=explode(",",$_GET["diff"]);array_splice($d,count($d)-1,1);
    $problem_controller=new Problem_Controller;
    $status_controller=new Status_Controller;
    $tags_controller=new Tags_Controller;
    $login_controller=new Login_Controller;
    $page=$param["page"]; $config=GetConfig(); $num=0;
    $problem_list=$problem_controller->ListProblemByNumber(
        ($page-1)*$config["number_of_pages"]+1,
        $page*$config["number_of_pages"],
        $_GET["key"],$t,$d,$num
    ); $pages_num=($num+$config["number_of_pages"]-1)/$config["number_of_pages"];
    $pages_num=intval($pages_num);
    if ($page<=0||$page>$pages_num) $page=1; 
    $problem_list=$problem_controller->ListProblemByNumber(
        ($page-1)*$config["number_of_pages"]+1,
        $page*$config["number_of_pages"],
        $_GET["key"],$t,$d,$num
    ); $search_box=InsertSingleTag("input",array("id"=>"key","placeholder"=>"Searching something...","value"=>$param["key"])).
    InsertTags("button",array("onclick"=>"search()","style"=>InsertInlineCssStyle(array(
        "margin-left"=>"10px",
        "height"=>"33px"
    ))),"Search");
    $search_box=InsertTags("div",array("class"=>"flex"),$search_box);
    $tags=$tags_controller->ListProblemTag();
    $tag_box=""; for ($i=0;$i<count($tags);$i++)
        $tag_box.=InsertTags("div",array("class"=>"problem-tags".(array_search($tags[$i],$t)===false?" unsubmitted":"")
        ,"id"=>"tag-".$tags[$i],"onclick"=>"addTag('".$tags[$i]."')"),$tags[$i]);
    $search_box.=InsertTags("div",null,InsertTags("p",array("style"=>InsertInlineCssStyle(array(
        "display"=>"inline-block"
    ))),"Tags Filter:&nbsp;&nbsp;").$tag_box); $tag_box="";
    for ($i=0;$i<count($config["difficulties"]);$i++)
        $tag_box.=InsertTags("div",array("class"=>"problem-difficulties-$i".(array_search($i,$d)===false?" unsubmitted":""),"id"=>"difficulties-$i",
        "onclick"=>"addDiff($i)"),$config["difficulties"][$i]["name"]);
    $search_box.=InsertTags("div",null,InsertTags("p",array("style"=>InsertInlineCssStyle(array(
        "display"=>"inline-block"
    ))),"Difficult Filter:&nbsp;&nbsp;").$tag_box);
    $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
        "padding-left"=>"20px",
        "margin-bottom"=>"20px",
        "padding-bottom"=>"15px",
        "padding-right"=>"20px",
        "width"=>"calc(100% - 20px)"
    ))),$search_box); $style=InsertCssStyle(array(".problem-item"),array(
            "width"=>"100%",
            "min-height"=>"50px",
            "background-color"=>"white",
            "padding-left"=>"20px",
            "margin-bottom"=>"20px",
            "align-items"=>"center"
    )); for ($i=0;$i<count($config["difficulties"]);$i++) {
        $style.=InsertCssStyle(array(".problem-difficulties-$i"),array(
            "background-color"=>$config["difficulties"][$i]["color"],
            "border-radius"=>"100px",
            "height"=>"25px",
            "color"=>"white",
            "padding-left"=>"10px",
            "padding-right"=>"10px",
            "font-size"=>"13px",
            "line-height"=>"25px",
            "margin-right"=>"5px",
            "width"=>"fit-content",
            "display"=>"inline-block",
            "cursor"=>"pointer"
        ));
    } $style.=InsertCssStyle(array(".problem-tags"),array(
        "background-color"=>"rgb(41,73,180)",
        "border-radius"=>"100px",
        "height"=>"25px",
        "color"=>"white",
        "padding-left"=>"10px",
        "padding-right"=>"10px",
        "font-size"=>"13px",
        "line-height"=>"25px",
        "margin-right"=>"4px",
        "margin-bottom"=>"4px",
        "width"=>"fit-content",
        "display"=>"inline-block",
        "cursor"=>"pointer"
    )); $style.=InsertCssStyle(array(".green"),array(
        "color"=>"green"
    )); $style.=InsertCssStyle(array(".grey"),array(
        "color"=>"rgba(0,0,0,0.7)"
    )); $style.=InsertCssStyle(array(".unsubmitted"),array(
        "background-color"=>"rgb(210,210,210)"
    )); for ($i=0;$i<count($problem_list);$i++) {
        $uid=$login_controller->CheckLogin();
        if ($problem_list[$i]["accepted"]) $tmp=InsertTags("p",array("style"=>InsertInlineCssStyle(array("width"=>"3%","cursor"=>"pointer")),"onclick"=>"location.href='".
        GetUrl("status",array("uid"=>$uid,"pid"=>$problem_list[$i]["id"]))."'"),InsertTags("i",array("class"=>"check icon green"),null));
        else $tmp=InsertTags("p",array("style"=>InsertInlineCssStyle(array("width"=>"3%"))),InsertTags("i",array("class"=>"minus icon grey"),null));
        $tmp.=InsertTags("p",array("style"=>InsertInlineCssStyle(array("width"=>"7%","cursor"=>"pointer")),
        "onclick"=>"location.href='".GetUrl("problem",array("id"=>$problem_list[$i]["id"]))."'"),"P".$problem_list[$i]["id"]);
        $tmp.=InsertTags("p",array(
            "style"=>InsertInlineCssStyle(array("width"=>"40%","cursor"=>"pointer")),
            "onclick"=>"location.href='".GetUrl("problem",array("id"=>$problem_list[$i]["id"]))."'",
            "class"=>"ellipsis"
        ),$problem_list[$i]["name"]); $tags_content="";
        $tags=$tags_controller->ListProblemTagsByPid($problem_list[$i]["id"]);
        if ($tags!=null) for ($j=0;$j<count($tags);$j++) $tags_content.=InsertTags("div",array("class"=>"problem-tags","onclick"=>"searchTag('".$tags[$j]."')"),$tags[$j]);
        $tmp.=InsertTags("div",array("style"=>InsertInlineCssStyle(array("width"=>"20%","padding-top"=>"12.5px","padding-bottom"=>"8.5px"))),$tags_content);
        $tmp.=InsertTags("div",array("style"=>InsertInlineCssStyle(array("width"=>"15%","text-align"=>"center"))),
        InsertTags("div",array("class"=>"problem-difficulties-".$problem_list[$i]["difficult"],"style"=>InsertInlineCssStyle(array(
        "margin"=>"auto")),"onclick"=>"searchDiff('".$problem_list[$i]["difficult"]."')"),$config["difficulties"][$problem_list[$i]["difficult"]]["name"]));
        $accepted=count($status_controller->ListAcceptedByPid($problem_list[$i]["id"]));
        $whole=count($status_controller->ListWholeByPid($problem_list[$i]["id"]));
        $tmp.=InsertTags("div",array("style"=>InsertInlineCssStyle(array("width"=>(10*$accepted/($whole?$whole:1))."%","height"=>"15px","background-color"=>"rgb(126,204,89)"))),"");
        $tmp.=InsertTags("div",array("style"=>InsertInlineCssStyle(array("width"=>(10-10*$accepted/($whole?$whole:1))."%","height"=>"15px","background-color"=>"rgb(232,232,232)"))),"");
        $body.=InsertTags("div",array(
            "class"=>"problem-item default_main flex",
        ),$tmp);
    } $content=""; $style.=InsertCssStyle(array(".pages"),array(
        "height"=>"30px",
        "line-height"=>"30px",
        "border"=>"1px solid",
        "border-color"=>"rgb(213,216,218)",
        "color"=>"rgb(27,116,221)",
        "margin-top"=>"10px",
        "margin-bottom"=>"10px",
        "padding-left"=>"20px",
        "padding-right"=>"20px",
        "border-radius"=>"3px",
        "font-weight"=>"500",
        "font-size"=>"13px",
        "background-color"=>"white",
        "cursor"=>"pointer",
        "transition"=>"background-color 0.5s,color 0.5s,border-color 0.5s"
    )).InsertCssStyle(array(".banned"),array(
        "color"=>"rgb(137,182,234)"
    )).InsertCssStyle(array(".pages:not(.banned):hover"),array(
        "background-color"=>"rgb(27,116,221)",
        "color"=>"white",
        "border-color"=>"rgb(27,116,221)"
    )); $content.=InsertTags("div",array("class"=>"pages","onclick"=>"location.href='".GetUrl("problem",array("page"=>1,"key"=>$param["key"],"tag"=>$param["tag"],"diff"=>$param["diff"]))."'",
    "style"=>InsertInlineCssStyle(array("margin-right"=>"10px"))),InsertTags("p",null,"Top"));
    if ($page==1) $content.=InsertTags("div",array("class"=>"pages banned"),InsertTags("p",null,"Previous"));
    else $content.=InsertTags("div",array("class"=>"pages","onclick"=>"location.href='".GetUrl("problem",array("page"=>$page-1,"key"=>$param["key"],"tag"=>$param["tag"],"diff"=>$param["diff"]))."'"),InsertTags("p",null,"Previous"));
    if ($page==$pages_num) $content.=InsertTags("div",array("class"=>"pages banned"),InsertTags("p",null,"Next"));
    else $content.=InsertTags("div",array("class"=>"pages","onclick"=>"location.href='".GetUrl("problem",array("page"=>$page+1,"key"=>$param["key"],"tag"=>$param["tag"],"diff"=>$param["diff"]))."'"),InsertTags("p",null,"Next"));
    $content.=InsertTags("div",array("class"=>"pages","onclick"=>"location.href='".GetUrl("problem",array("page"=>$pages_num,"key"=>$param["key"],"tag"=>$param["tag"],"diff"=>$param["diff"]))."'",
    "style"=>InsertInlineCssStyle(array("margin-left"=>"10px"))),InsertTags("p",null,"Bottom"));
    $body.=InsertTags("div",array("class"=>"flex","style"=>InsertInlineCssStyle(array(
        "justify-content"=>"center"
    ))),$content);
    $body.=InsertTags("style",null,$style);
    $script="var tag=new Array,diff=new Array;";
    for ($i=0;$i<count($t);$i++) $script.="tag.push('".$t[$i]."');";
    for ($i=0;$i<count($d);$i++) $script.="diff.push(".$d[$i].");";
    $script.="function searchTag(name) {";
    $script.="tag=new Array; diff=new Array;";
    $script.="addTag(name); search();}";
    $script.="function searchDiff(name) {";
    $script.="tag=new Array; diff=new Array;";
    $script.="addDiff(name); search();}";
    $script.="function addTag(name) {";
    $script.="if (!tag.includes(name)) {document.getElementById('tag-'+name).classList.remove('unsubmitted');tag.push(name);}";
    $script.="else {document.getElementById('tag-'+name).classList.add('unsubmitted');tag=tag.filter(function(item){return item!=name});}";
    $script.="}";
    $script.="function addDiff(id) {";
    $script.="if (!diff.includes(id)) {document.getElementById('difficulties-'+id).classList.remove('unsubmitted');diff.push(id);}";
    $script.="else {document.getElementById('difficulties-'+id).classList.add('unsubmitted');diff=diff.filter(function(item){return item!=id});}";
    $script.="}";
    $script.="function search() {";
    $script.="var url='".GetUrl("problem",array("page"=>1))."&key='+encodeURIComponent(document.getElementById('key').value);";
    $script.="url+='&tag='; for (i=0;i<tag.length;i++) url+=encodeURIComponent(tag[i]+',');";
    $script.="url+='&diff='; for (i=0;i<diff.length;i++) url+=encodeURIComponent(diff[i]+',');";
    $script.="window.location.href=url;";
    $script.="}";
    $script.="$(\"#key\").keypress(function(event){";
    $script.="var keynum=(event.keyCode?event.keyCode:event.which);  ";
    $script.="if(keynum=='13') search();";
    $script.="});";
    $body.=InsertTags("script",null,$script);
}

function format_size(int $size):string {
    if ($size<=2048) return $size."B";
    $size=$size/1024; if ($size<100) return round($size,2)."KB";
    if ($size<1000) return round($size,1)."KB";
    if ($size<=2048) return round($size,0)."KB";
    $size=$size/1024; if ($size<100) return round($size,2)."MB";
    if ($size<1000) return round($size,1)."MB";
    if ($size<=2048) return round($size,0)."MB";
    $size=$size/1024; return round($size,0)."GB";
}

function info_run(array $param,string &$html,string &$body):void {
    $config=GetConfig();
    $problem_controller=new Problem_Controller;    
    $login_controller=new Login_Controller;
    $status_controller=new Status_Controller;
    $cid=0; if (array_key_exists("contest",$param)) $cid=$param["contest"];
    $info=$problem_controller->ListProblemByPid($param["id"],false,$cid);
    $fp=fopen("../problem/".$info["id"]."/config.json","r");
    $conf=fread($fp,filesize("../problem/".$info["id"]."/config.json"));
    $conf=JSON_decode($conf,true);
    $min_t=1e18;$max_t=-1e18;$min_m=1e18;$max_m=-1e18;
    $info_res="Input: ".$conf["input"]." | Output: ".$conf["output"]." | ";
    if (count($conf["data"])!=0) {
        for ($i=0;$i<count($conf["data"]);$i++) {
            $min_t=min($min_t,$conf["data"][$i]["time"]);
            $max_t=max($max_t,$conf["data"][$i]["time"]);
            $min_m=min($min_m,$conf["data"][$i]["memory"]);
            $max_m=max($max_m,$conf["data"][$i]["memory"]);
        } $info_res.="Time: ".(($min_t==$max_t)?$min_t."ms":$min_t."ms~".$max_t."ms");$info_res.=" | ";
        $info_res.="Memory: ".(($min_m==$max_m)?($min_m/1024)."mb":($min_m/1024)."mb~".($max_m/1024)."mb");
    } else $info_res.="Time: No Test Data | Memory: No Test Data";
    $title=InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
        "padding-left"=>"20px",
        "padding-right"=>"20px",
        "font-size"=>"25px",
        "font-weight"=>"400"
    ))),"P".$info["id"]." - ".$info["name"]).
    InsertTags("p",array("style"=>InsertInlineCssStyle(array(
        "padding-left"=>"20px",
        "padding-right"=>"20px",
        "margin-top"=>"5px",
    ))),htmlentities($info_res));
    $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
        "padding-top"=>"20px",
        "margin-bottom"=>"20px",
        "padding-bottom"=>"20px",
    ))),$title);

    if ($info["bg"]!="") {
        $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
            "padding-top"=>"20px",
            "margin-bottom"=>"20px"
        ))),InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
            "padding-bottom"=>"60px",
            "padding-left"=>"20px",
            "padding-right"=>"20px"
        ))),"Background").InsertTags("div",array("id"=>"problem-background","style"=>InsertInlineCssStyle(array(
            "width"=>"calc(100% - 40px)"
        ))),""));
        $script=md2html($info["bg"],"problem-background");
    }

    if ($info["descrip"]!="") {
        $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
            "padding-top"=>"20px",
            "margin-bottom"=>"20px"
        ))),InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
            "padding-bottom"=>"60px",
            "padding-left"=>"20px",
            "padding-right"=>"20px"
        ))),"Description").InsertTags("div",array("id"=>"problem-description","style"=>InsertInlineCssStyle(array(
            "width"=>"calc(100% - 40px)"
        ))),""));
        $script.=md2html($info["descrip"],"problem-description");
    }

    if ($info["input"]!="") {
        $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
            "padding-top"=>"20px",
            "margin-bottom"=>"20px"
        ))),InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
            "padding-bottom"=>"60px",
            "padding-left"=>"20px",
            "padding-right"=>"20px"
        ))),"Input").InsertTags("div",array("id"=>"problem-input","style"=>InsertInlineCssStyle(array(
            "width"=>"calc(100% - 40px)"
        ))),""));
        $script.=md2html($info["input"],"problem-input");
    }

    if ($info["output"]!="") {
        $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
            "padding-top"=>"20px",
            "margin-bottom"=>"20px"
        ))),InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
            "padding-bottom"=>"60px",
            "padding-left"=>"20px",
            "padding-right"=>"20px"
        ))),"Output").InsertTags("div",array("id"=>"problem-output","style"=>InsertInlineCssStyle(array(
            "width"=>"calc(100% - 40px)"
        ))),""));
        $script.=md2html($info["output"],"problem-output");
    }

    $style=InsertCssStyle(array(".problem-cases"),array(
        "border"=>"rgb(221,221,221) solid 1px",
        "background-color"=>"rgb(249,255,204)",
        "overflow"=>"auto",
        "border-radius"=>"3px",
        "font-size"=>"15px",
        "font-family"=>"monospace",
        "margin"=>"7px 0",
        "padding"=>"8.4px 10px",
        "width"=>"calc(100% - 20px)"
    ));
    $info["cases"]=preg_replace('/[[:cntrl:]]/','',$info["cases"]);
    $sample=JSON_decode($info["cases"],true); $res="";
    if (count($sample)!=0) {
        for ($i=0;$i<count($sample);$i++) {
            $sample[$i]["input"]=str_replace("\\n","<br/>",$sample[$i]["input"]);
            $sample[$i]["output"]=str_replace("\\n","<br/>",$sample[$i]["output"]);
            $tmp=InsertTags("div",array("style"=>InsertInlineCssStyle(array("width"=>"49%","margin-right"=>"2%"))),
            InsertTags("p",null,"Input #".($i+1).":").InsertTags("pre",array("class"=>"problem-cases","id"=>"problem-cases-input".($i+1)),$sample[$i]["input"]));
            $tmp.=InsertTags("div",array("style"=>InsertInlineCssStyle(array("width"=>"49%"))),
            InsertTags("p",null,"Output #".($i+1).":").InsertTags("pre",array("class"=>"problem-cases","id"=>"problem-cases-output".($i+1)),$sample[$i]["output"]));
            $res.=InsertTags("div",array("class"=>"flex","style"=>InsertInlineCssStyle(array(
                "padding-left"=>"20px",
                "padding-right"=>"20px",
                "margin-top"=>"10px",
                "align-items"=>"baseline"
            ))),$tmp);
        }
        $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
            "padding-top"=>"20px",
            "margin-bottom"=>"20px"
        ))),InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
            "padding-bottom"=>"60px",
            "padding-left"=>"20px",
            "padding-right"=>"20px"
        ))),"Sample").InsertTags("div",array("id"=>"problem-sample","style"=>InsertInlineCssStyle(array(
            "width"=>"calc(100% - 40px)",
            "padding-bottom"=>"20px"
        ))),$res));
    }

    if ($info["hint"]!="") {
        $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
            "padding-top"=>"20px",
            "margin-bottom"=>"20px"
        ))),InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
            "padding-bottom"=>"60px",
            "padding-left"=>"20px",
            "padding-right"=>"20px"
        ))),"Hint").InsertTags("div",array("id"=>"problem-hint","style"=>InsertInlineCssStyle(array(
            "width"=>"calc(100% - 40px)"
        ))),""));
        $script.=md2html($info["hint"],"problem-hint");
    }

    if (is_dir("./files/".$param["id"])) $files=scandir("./files/".$param["id"]);
    else $files=array(".","..");
    if (count($files)>2) {
        $tmp=""; foreach($files as $key=>$value) {
            if (is_dir("./files/".$param["id"]."/$value")) continue;
            $tmp.=InsertTags("i",array("class"=>"linkify icon","style"=>InsertInlineCssStyle(array(
                "width"=>"auto",
                "margin"=>"10px 20px 10px 20px"
            ))),"&nbsp;&nbsp;&nbsp;&nbsp;".
            InsertTags("a",array("href"=>GetAPIUrl("/problem/addition",array("pid"=>$param["id"],"name"=>$value))),$value).
            "&nbsp;&nbsp;".InsertTags("pp",null,format_size(filesize("./files/".$param["id"]."/$value")))
            ).InsertSingleTag("br",null);
        }
        $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
            "padding-top"=>"20px",
            "padding-bottom"=>"20px",
            "margin-bottom"=>"20px"
        ))),InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
            "padding-bottom"=>"60px",
            "padding-left"=>"20px",
            "padding-right"=>"20px",
            "margin-bottom"=>"10px"
        ))),"Additional Files").InsertSingleTag("br",null).$tmp);
    }

    if ($login_controller->CheckLogin()) {
        $uid=$login_controller->CheckLogin(); $sum=0;
        $array=$status_controller->GetJudgeInfo(1,1,$uid,$info["id"],$sum);
        $tmp=""; for ($i=0;$i<count($config["lang"]);$i++) 
        if ($i!=$config["default_lang"]) $tmp.=InsertTags("option",array("value"=>$i),$config["lang"][$i]["name"]);
        else $tmp.=InsertTags("option",array("value"=>$i,"selected"=>"selected"),$config["lang"][$i]["name"]);
        $tmp=InsertTags("div",array("class"=>"flex"),InsertTags("p",null,"Choose Language:&nbsp").
            InsertTags("select",array("id"=>"language","style"=>InsertInlineCssStyle(array(
            "width"=>"10px",
            "flex-grow"=>"1000",
            "height"=>"30px",
            "padding-left"=>"10px",
            "outline"=>"none",
            "border"=>"rgb(221,221,221) 1px solid",
            "background-color"=>"rgb(249,255,204)",
            "border-radius"=>"3px",
            "padding-right"=>"10px"
        ))),$tmp));
        $tmp.=InsertTags("div",array("id"=>"code-container","style"=>InsertInlineCssStyle(array(
            "height"=>"500px",
            "min-height"=>"inherit",
            "margin-top"=>"inherit",
            "margin-bottom"=>"inherit",
            "margin-top"=>"10px"
        ))),""); $tmp.=InsertTags("center",null,
        InsertTags("button",array("onclick"=>"submit()"),"Submit"));
        $script.="var codeEditor=null;"; $code="";
        if ($sum!=0) $code=$array[0]["code"];
        $code=str_replace("\\","\\\\",$code); $code=str_replace("\n","\\n",$code); 
        $code=str_replace("\r","",$code); $code=str_replace("'","\\'",$code);
        $script.="addLoadEvent(function(){codeEditor=monaco.editor.create(".
        "document.getElementById('code-container'),{language:'".$config["lang"][$config["default_lang"]]["mode"]."',roundedSelection:false,".
        "scrollBeyondLastLine:false,readOnly:false,theme:'vs-dark'});".
        "document.getElementsByClassName(\"overflow-guard\")[0].style.width='100%';".
        "document.getElementsByClassName(\"monaco-editor\")[0].style.width='100%';".
        "codeEditor.setValue('$code')});";
        $body.=InsertTags("div",array("class"=>"default_main","style"=>InsertInlineCssStyle(array(
            "padding-top"=>"20px",
            "margin-bottom"=>"20px"
        ))),InsertTags("hp",array("style"=>InsertInlineCssStyle(array(
            "padding-bottom"=>"60px",
            "padding-left"=>"20px",
            "padding-right"=>"20px"
        ))),"Submit&nbsp;".InsertTags("button",array("onclick"=>"location.href='".
        GetUrl("status",array("uid"=>$uid,"pid"=>$param["id"],"page"=>1))."'","style"=>
        InsertInlineCssStyle(array("width"=>"100px","height"=>"30px",
        "line-height"=>"20px",
        "padding-left"=>"0px",
        "padding-right"=>"0px",
        "padding-top"=>"2px",
        "position"=>"relative",
        "top"=>"-2px"))),"My Submit"))
        .InsertTags("div",array("id"=>"problem-submit","style"=>InsertInlineCssStyle(array(
            "width"=>"calc(100% - 40px)",
            "padding-left"=>"20px",
            "padding-right"=>"20px",
            "padding-top"=>"10px",
            "padding-bottom"=>"20px"
        ))),$tmp));
        $script.="var mode=["; for ($i=0;$i<count($config["lang"])-1;$i++) $script.="'".$config["lang"][$i]["mode"]."',";
        $script.="'".$config["lang"][count($config["lang"])-1]["mode"]."'];";
        $script.="document.getElementById('language').onchange=function(){".
        "monaco.editor.setModelLanguage(codeEditor.getModel(),mode[document.getElementById('language').value])};";
        $script.="function submit(){var lang=document.getElementById('language').value,code=codeEditor.getValue();".
		"console.log(lang);console.log(code);var e=new RegExp(\"\\\\\\\\\",\"g\");code=code.replace(e,\"\\\\\\\\\");".
        "e=new RegExp(\"'\",\"g\");code=code.replace(e,\"\\\\'\");var res=SendAjax(\"".GetAPIUrl("/problem/submit")."\",'POST',{".
        "code:code,lang:lang,pid:".$param["id"].",cid:".(array_key_exists("contest",$param)?$param["contest"]:0)."});".
        "if (res==null) layui.msg('Submit Failed!'); else {".
        "res=JSON.parse(strip_tags(res));if (res[\"code\"]) {layer.msg(res[\"message\"]);return false;}".
        "location.href='".GetUrl("status",array("id"=>""))."'+res['data']['id'];}}";
    }
    $script.="document.title='".$info["name"]." - ".$config["web"]["title"]."'";

    $body.=InsertTags("style",null,$style);
    $body.=InsertTags("script",null,$script);
}
?>
