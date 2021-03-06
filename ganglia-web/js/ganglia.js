$(function(){
  // Ensure that the window has a unique name
  if ((window.name == null) || window.name == "") {
    var d = new Date();
    window.name = d.getTime();
  }

  // Follow tab's URL instead of loading its content via ajax
  var tabs = $("#tabs");
  if (tabs[0]) {
    tabs.tabs();
    // Restore previously selected tab
    var selected_tab = $.cookie("ganglia-selected-tab-" + window.name);
    if ((selected_tab != null) && (selected_tab.length > 0)) {
      try {
        var tab_index = parseInt(selected_tab, 10);
        if (!isNaN(tab_index) && (tab_index >= 0)) {
          //alert("ganglia-selected-tab: " + tab_index);
          tabs.tabs("select", tab_index);
          switch (tab_index) {
            case 3:
              autoRotationChooser();
              break;
          }
        }
      } catch (err) {
        try {
          alert("Error(ganglia.js): Unable to select tab: " + 
                tab_index + ". " + err.getDescription());
        } catch (err) {
          // If we can't even show the error, fail silently.
        }
      }
    }

    tabs.bind("tabsselect", function(event, ui) {
      // Store selected tab in a session cookie
      $.cookie("ganglia-selected-tab-" + window.name, ui.index);
    });
  }

  var range_menu = $( "#range_menu" );
  if (range_menu[0])
    range_menu.buttonset();
  var sort_menu = $( "#sort_menu" );
  if (sort_menu[0])
    sort_menu.buttonset();

  var metric_search_input = jQuery('#metric-search input[name="q"]');
  if (metric_search_input[0])
    metric_search_input.liveSearch({url: 'search.php?q=', typeDelay: 500});

  var search_field_q = $( "#search-field-q");
  if (search_field_q[0]) {
    search_field_q.keyup(function() {
      $.cookie("ganglia-search-field-q" + window.name, $(this).val());
    });

    var search_value = $.cookie("ganglia-search-field-q" + window.name);
    if (search_value != null && search_value.length > 0)
      search_field_q.val(search_value);
  }

  var datepicker_cs = $( "#datepicker-cs" );
  if (datepicker_cs[0])
    datepicker_cs.datepicker({
	  showOn: "button",
	  constrainInput: false,
	  buttonImage: "img/calendar.gif",
	  buttonImageOnly: true
    });

  var datepicker_ce = $( "#datepicker-ce" );
  if (datepicker_ce[0])
    datepicker_ce.datepicker({
	  showOn: "button",
	  constrainInput: false,
	  buttonImage: "img/calendar.gif",
	  buttonImageOnly: true
    });

  var create_new_view_dialog = $( "#create-new-view-dialog" );
  if (create_new_view_dialog[0])
    create_new_view_dialog.dialog({
      autoOpen: false,
      height: 200,
      width: 350,
      modal: true,
      close: function() {
        getViewsContent();
        $("#create-new-view-layer").toggle();
        $("#create-new-view-confirmation-layer").html("");
      }
    });

  var metric_actions_dialog = $( "#metric-actions-dialog" );
  if (metric_actions_dialog[0]) 
    metric_actions_dialog.dialog({
      autoOpen: false,
      height: 250,
      width: 450,
      modal: true
    });
});

function selectTab(tab_index) {
  $("#tabs").tabs("select", tab_index);
}

function viewId(view_name) {
  return "v_" + view_name.replace(/[^a-zA-Z0-9_]/g, "_");
}

function highlightSelectedView(view_name) {
  $("#navlist a").css('background-color', '#FFFFFF');	
  $("#" + viewId(view_name)).css('background-color', 'rgb(238,238,238)');
}

function selectView(view_name) {
  highlightSelectedView(view_name);
  $.cookie('ganglia-selected-view-' + window.name, view_name); 
  var range = $.cookie('ganglia-view-range-' + window.name);
  if (range == null)
    range = '1hour';
  getViewsContentJustGraphs(view_name, range, '', '');
}

function getViewsContent() {
  $.get('views.php', "" , function(data) {
    $("#tabs-views-content").html('<img src="img/spinner.gif">');
    $("#tabs-views-content").html(data);
/*
    $("#tabs-views-content").html(
      '<div class="ui-widget">' +
        '<div class="ui-state-default ui-corner-all" style="padding: 0 .7em;">
          '<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>' +
        '</div>' +
      '</div>'
    );
*/

    $("#create_view_button")
      .button()
      .click(function() {
	$( "#create-new-view-dialog" ).dialog( "open" );
      });;
    $( "#view_range_chooser" ).buttonset();

    // Restore previously selected view
    var view_name = document.getElementById('view_name');
    var selected_view = $.cookie("ganglia-selected-view-" + window.name);
    if (selected_view != null) {
        view_name.value = selected_view;
	var range = $.cookie("ganglia-view-range-" + window.name);
	if (range == null)
          range = "hour";
	$("#view-range-"+range).click();
    } else
      view_name.value = "default";
    highlightSelectedView(view_name.value);
  });
  return false;
}

// This one avoids 
function getViewsContentJustGraphs(viewName,range, cs, ce) {
    $("#view_graphs").html('<img src="img/spinner.gif">');
    $.get('views.php', "view_name=" + viewName + "&just_graphs=1&r=" + range + "&cs=" + cs + "&ce=" + ce, function(data) {
	$("#view_graphs").html(data);
	document.getElementById('view_name').value = viewName;
     });
    return false;
}

function createView() {
  $("#create-new-view-confirmation-layer").html('<img src="img/spinner.gif">');
  $.get('views.php', $("#create_view_form").serialize() , function(data) {
    $("#create-new-view-layer").toggle();
    $("#create-new-view-confirmation-layer").html(data);
  });
  return false;
}

function addItemToView() {
  $.get('views.php', $("#add_metric_to_view_form").serialize() + "&add_to_view=1" , function(data) {
      $("#metric-actions-dialog-content").html('<img src="img/spinner.gif">');
      $("#metric-actions-dialog-content").html(data);
  });
  return false;  
}
function metricActions(host_name,metric_name,type,graphargs) {
    $( "#metric-actions-dialog" ).dialog( "open" );
    $("#metric-actions-dialog-content").html('<img src="img/spinner.gif">');
    $.get('actions.php', "action=show_views&host_name=" + host_name + "&metric_name=" + metric_name + "&type=" + type + graphargs, function(data) {
      $("#metric-actions-dialog-content").html(data);
     });
    return false;
}

function createAggregateGraph() {
  if ( $('#hreg').val() == "" ||  $('#metric_chooser').val() == "" ) {
      alert("Host regular expression and metric name can't be blank");
      return false;
  }
  $("#aggregate_graph_display").html('<img src="img/spinner.gif">');
  $.get('graph_all_periods.php', $("#aggregate_graph_form").serialize() + "&aggregate=1&embed=1" , function(data) {
    $("#aggregate_graph_display").html(data);
  });
  return false;
}

function metricActionsAggregateGraph(args) {
    $( "#metric-actions-dialog" ).dialog( "open" );
    $("#metric-actions-dialog-content").html('<img src="img/spinner.gif">');
    $.get('actions.php', "action=show_views" + args, function(data) {
      $("#metric-actions-dialog-content").html(data);
     });
    return false;
}


function autoRotationChooser() {
  $("#tabs-autorotation-chooser").html('<img src="img/spinner.gif">');
  $.get('autorotation.php', "" , function(data) {
      $("#tabs-autorotation-chooser").html(data);
  });
}
function updateViewTimeRange() {
  alert("Not implemented yet");
}

function ganglia_submit(clearonly) {
  document.getElementById("datepicker-cs").value = "";
  document.getElementById("datepicker-ce").value = "";
  if (! clearonly)
    document.ganglia_form.submit();
}

function detachViews() {
  var selected_view = $.cookie("ganglia-selected-view-" + window.name);
  var href = "views.php?standalone=1";
  if (selected_view != null)
    href += "&view_name=" + encodeURIComponent(selected_view);
  location.href = href;
}

/* ----------------------------------------------------------------------------
 Enlarges a graph using Flot
-----------------------------------------------------------------------------*/
function enlargeGraph(graphArgs) {
  $("#enlarge-graph-dialog").dialog('open');
  $("#enlarge-graph-dialog").bind( "dialogbeforeclose", function(event, ui) {
    $("#enlargeTooltip").remove();
  });
//  $('#enlarge-graph-dialog-content').html('<img src="graph.php?' + graphArgs + '" />');
  $.get('inspect_graph.php', "flot=1&" + graphArgs, function(data) {
    $('#enlarge-graph-dialog-content').html(data);
  })
}
