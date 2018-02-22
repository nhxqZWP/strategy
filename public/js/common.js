var subformCheck = function(domname, targname) {
    if(!targname || targname=="") targname = ".subitem";
    var paramlist={}, tempv;
    var flg=true;
    $("#"+domname).find(targname).each(function(){
        switch($(this)[0].tagName){
            case "IMG": {
                if($.trim($(this).attr("data-w"))){
                    paramlist[$(this).attr("itemname")]=$.trim($(this).attr("src"));
                    paramlist[$(this).attr("itemname")+"-w"]=$.trim($(this).attr("data-w"));
                    paramlist[$(this).attr("itemname")+"-h"]=$.trim($(this).attr("data-h"));
                }else{
                    flg=false;
                    alert($(this).attr("errmsg"));
                    return false;
                }
                break;
            }
            default:{
                if($(this).attr("type") == 'radio'){
                    tempv = $(':radio[name="'+$(this).attr("name")+'"]:checked').val();
                }else{
                    tempv = $.trim($(this).val());
                }
                if(tempv){
                    if(tempv == 'targparam'){
                        tempv = $.trim($("#"+$(this).attr("data-targ")).val());
                        if(tempv)
                            paramlist[$(this).attr("itemname")] = tempv;
                        else{
                            flg=false;
                            alert($(this).attr("errmsg"));
                            return false;
                        }
                    }else{
                        paramlist[$(this).attr("itemname")] = tempv;
                    }
                }else{
                    flg=false;
                    alert($(this).attr("errmsg"));
                    return false;
                }
            }
        }
    });
    if(flg){
        return paramlist;
    }else{
        return null;
    }
}
$(function () {
    $('.deleteConfirm').click(function () {
        var that = $(this);
        bootbox.confirm("are you sure?", function (result) {
            var url = that.attr('href');
            if (result) {
                window.location.href = url;
            }
        });

        return false;
    });

    $('.img-popup').click(function(){
        var src = $(this).attr('data-src');

        bootbox.dialog({
            title: "Full Image",
            size: 'large',
            message: '<img src="'+src+'" style="width: 100%;"/>'
        });
        return false;
    });

    
});
