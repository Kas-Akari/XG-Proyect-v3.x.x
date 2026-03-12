<script>
messageboxHeight=0;
errorboxHeight=0;
contentbox = document.getElementById('content');
</script>

<div id='messagebox'>
<center>
</center>
</div>
<div id='errorbox'>
<center>
</center>
</div>

<script>
headerHeight = 81;
errorbox.style.top=parseInt(headerHeight+messagebox.offsetHeight+5)+'px';
contentbox.style.top=parseInt(headerHeight+errorbox.offsetHeight+messagebox.offsetHeight+10)+'px';

function adjustContentHeight() {
    contentbox.style.height = parseInt(window.innerHeight) - messagebox.offsetHeight - errorbox.offsetHeight - headerHeight - 20 + 'px';
}

adjustContentHeight();
window.addEventListener('resize', adjustContentHeight);

for (var i = 0; i < document.links.length; ++i) {
  if (document.links[i].href.search(/.*redir\.php\?url=.*/) != -1) {
    document.links[i].target = "_blank";
  }
}

</script>
</body>
<html>
