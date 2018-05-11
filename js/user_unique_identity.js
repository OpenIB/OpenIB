if (active_page == 'thread' || active_page == 'index' || active_page == 'catalog') {
    $(document).on('menu_ready', function(){
    function createCookie(name, value, days) {
        var date, expires;
        if (days) {
            date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            expires = "; expires="+date.toGMTString();
        } else {
            expires = "";
        }
        document.cookie = name+"="+value+expires+"; path=/";
    }
    function getCookie(name){
        var re = new RegExp(name + "=([^;]+)");
        var value = re.exec(document.cookie);
        return (value != null) ? unescape(value[1]) : null;
        }
        
        //check if cookie pass is null, then create
        if(getCookie("uid")==null){
                
            //generate 
            uid = generatePassword();
            createCookie("uid", uid, 1);
        }
        $(document).on('ajax_after_post', function(e, post) {
          if(post.post_type){
            localStorage.new_thread = 1;
                    localStorage.new_thread_pass = localStorage.password;
          }
        });
        function get_thread_id(path_str){
            var pnt_arr = path_str.split("/");
            var newest_thread_id = pnt_arr[pnt_arr.length-1].replace(".html","");
            return newest_thread_id;
        }
        var genhs = function(guid,rnd){
            if(rnd==1){
                var random_string = generatePassword();
                var str_id = random_string + getCookie("uid") + guid;
                var new_pass = SHA1 (str_id).slice(-16);
            }else{
                var new_pass = SHA1 (getCookie("uid") + guid).slice(-16);
            }
            document.forms.post.password.value = new_pass;
            localStorage.password = new_pass;
        }
        if(localStorage.new_thread == 1){
                localStorage.new_thread_id = get_thread_id(window.location.pathname);
    
        }
        var url_path = window.location.pathname;
        //check if new_thread_id in localstorage is not equal to viewed page
        var get_viewed_thread_id = get_thread_id( url_path );
        var nav = window.navigator;
        var guid = nav.userAgent.replace(/[^a-zA-Z0-9]/g, '');
        guid += url_path.replace(/[^a-zA-Z0-9]/g, '');
        if(get_viewed_thread_id == localStorage.new_thread_id){
            document.forms.post.password.value = localStorage.new_thread_pass;
            localStorage.password = localStorage.new_thread_pass;   
        }else{
            if(active_page == 'index' || active_page == 'catalog'){
                genhs(guid,1);
            }else{
                genhs(guid,0);
            }
        }
        //reset
        localStorage.new_thread = 0;
    });
}
