<?php global $path; ?>

<style>
.section {
  border-top:1px solid #ccc;
  border-left:1px solid #ccc;
  border-right:1px solid #ccc;
  text-align:left;
}

.section-heading {
  background-color:#eee;
  padding:10px;
  text-align:left;
  cursor:pointer;
}

#conf table {
  width:100%;
  border-collapse: collapse;
  background-color:#fcfcfc;
  text-align:left;
  border: 1px solid #ccc;
}

#conf th {
  text-align:left;
  border: 1px solid #ccc;
  padding:10px;
}

#conf td {
  text-align:left;
  border: 1px solid #ccc;
  padding:10px;
}

.input-prepend { margin:0px }
select { margin:0px; width:300px; }

</style>

<div id="wrapper">
  <?php include "Modules/config/sidebar.php"; ?>

  <div style="height:20px"></div>

  <div id="conf">
    <h2>Calibration</h2>
    <p>Adjust calibration for nodes running unitless firmware.</p>
    <div class='section' v-for="(node,nodeid) in conf.nodes">
      <div class='section-heading' v-bind:name='nodeid'><b>{{ nodeid }}:{{ node.nodename }}</b></div>
      <div style="padding:5px">
        <table class='section-content' v-bind:name='nodeid'>
          <tr>
            <th>Name</th>
            <th></th>
            <th>Calibration</th>
            <th>Phase Shift</th>
            <th>Value</th>
            <th>Time</th>
          </tr>

          <tr v-for="(input,index) in node.rx.unitless" v-if="input=='v'">
            <td>Voltage calibration:</td>
            <td><select><option>ACAC Ideal Power</option></select></td>
            <td><div class="input-prepend input-append">
              <button class="btn">-</button>
              <input type="text" style="width:70px" v-model="node.rx.vcal" />
              <button class="btn">+</button>
            </div></td>
            <td></td>
            <td><span v-if="typeof live[node.nodename]!=='undefined'" v-html="list_format_value(live[node.nodename][node.rx.names[index]].value)"></span>{{ node.rx.units[index] }}</td>
            <td><span v-if="typeof live[node.nodename]!=='undefined'" v-html="list_format_updated(live[node.nodename][node.rx.names[index]].time)"></span></td>
          </tr>
          
          <tr v-for="(input,index) in node.rx.unitless" v-if="input=='rp'">
            <td>{{ node.rx.names[index] }}</td>
            <td><select><option>SCT-013-000: 2000 turns, 22R burden</option><option>SCT-013-000: 2000 turns, 100R burden</option></select></td>
            <td><div class="input-prepend input-append">
              <button class="btn">-</button>
              <input type="text" style="width:70px" v-model="node.rx.icals[index]" />
              <button class="btn">+</button>
            </div></td>
            <td><div class="input-prepend input-append">
              <button class="btn">-</button>
              <input type="text" style="width:70px" v-model="node.rx.phase_shifts[index]" />
              <button class="btn">+</button>
            </div></td>
            <td><span v-if="typeof live[node.nodename]!=='undefined'" v-html="list_format_value(live[node.nodename][node.rx.names[index]].value)"></span>{{ node.rx.units[index] }}</td>
            <td><span v-if="typeof live[node.nodename]!=='undefined'" v-html="list_format_updated(live[node.nodename][node.rx.names[index]].time)"></span></td>
          </tr>       
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?php echo $path; ?>Lib/misc/sidebar.js"></script>
<link rel="stylesheet" href="<?php echo $path; ?>Lib/misc/sidebar.css">
<script src="<?php echo $path; ?>Modules/config/vue.js"></script>

<script>
init_sidebar({menu_element:"#config_menu"});

var path = "<?php echo $path; ?>";
var conf = <?php echo $conf; ?>;

var tmp = {};
for (var n in conf.nodes) {
    if (conf.nodes[n].rx.unitless!=undefined) {
        tmp[n] = conf.nodes[n];
    }
}
conf.nodes = tmp;
console.log(JSON.parse(JSON.stringify(conf)));

var app = new Vue({
  el: '#conf',
  data: { conf: conf, live: "hello" },
  filters: {
    dp2: function(value) {
      return value.toFixed(2);
    }
  },
  methods: {
    list_format_updated: function(value) {
      return list_format_updated(value)
    },
    list_format_value: function(value) {
      return list_format_value(value)
    }
  }
});

$("#conf").on('click',".section-heading",function(){
  var name = $(this).attr("name");
  $(".section-content[name='"+name+"']").toggle(); 
});

$("#conf").on("change","input",function() {
  var appconf = JSON.parse(JSON.stringify(app.$data.conf));
  console.log("change");
  $.ajax({ type: "POST", url: path+"config/setemonhub", data: "config="+JSON.stringify(appconf), async: false, success: function(data){ 
  // --- 
  }});
});

update();
setInterval(update,5000);
function update(){
    $.ajax({ type: "GET", url: path+"input/get", async: false, success: function(result){ 
        app.live = result;
    }});
}

// -------------------------------------------------------------------------

function list_format_updated(time) {
    time = time * 1000;
    var servertime = new Date().getTime(); // - table.timeServerLocalOffset;
    var update = new Date(time).getTime();
    
    var secs = (servertime - update) / 1000;
    var mins = secs / 60;
    var hour = secs / 3600;
    var day = hour / 24;
    
    var updated = secs.toFixed(0) + "s";
    if (update == 0 || !$.isNumeric(secs)) updated = "n/a";
    else if (secs < 0) updated = secs.toFixed(0) + "s";
    // update time ahead of server date is signal of slow network
    else if (secs.toFixed(0) == 0) updated = "now";
    else if (day > 7) updated = "inactive";
    else if (day > 2) updated = day.toFixed(1) + " days";
    else if (hour > 2) updated = hour.toFixed(0) + " hrs";
    else if (secs > 180) updated = mins.toFixed(0) + " mins";
    
    secs = Math.abs(secs);
    var color = "rgb(255,0,0)";
    if (secs < 25) color = "rgb(50,200,50)";
    else if (secs < 60) color = "rgb(240,180,20)";
    else if (secs < 3600 * 2) color = "rgb(255,125,20)";
    
    return "<span style='color:" + color + ";'>" + updated + "</span>";
}

function list_format_value(value) {
    if (value == null) return "NULL";
    value = parseFloat(value);
    if (value >= 1000) value = parseFloat(value.toFixed(0));
    else if (value >= 100) value = parseFloat(value.toFixed(1));
    else if (value >= 10) value = parseFloat(value.toFixed(2));
    else if (value <= -1000) value = parseFloat(value.toFixed(0));
    else if (value <= -100) value = parseFloat(value.toFixed(1));
    else if (value < 10) value = parseFloat(value.toFixed(2));
    return value;
}

</script>