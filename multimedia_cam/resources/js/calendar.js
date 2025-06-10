document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth'
    });
    calendar.render();
});


// document.addEventListener('DOMContentLoaded', function() {
//     var calendarEl = document.getElementById('calendar');

//     var calendar = new FullCalendar.Calendar(calendarEl, {
//         initialView: 'dayGridMonth',
//         editable: true,
//         headerToolbar: {
//             left: 'prev,next today',
//             center: 'title',
//             right: 'dayGridMonth,timeGridWeek,timeGridDay'
//         },
//         events: '/full-calender',
//         selectable: true,
//         selectHelper: true,
//         select: function(info) {
//             var title = prompt('Event Title:');
//             if (title) {
//                 var start = FullCalendar.formatDate(info.start, {
//                     year: 'numeric',
//                     month: '2-digit',
//                     day: '2-digit',
//                     hour: '2-digit',
//                     minute: '2-digit',
//                     second: '2-digit',
//                     meridiem: false,
//                     formatMatcher: 'basic'
//                 });

//                 var end = FullCalendar.formatDate(info.end, {
//                     year: 'numeric',
//                     month: '2-digit',
//                     day: '2-digit',
//                     hour: '2-digit',
//                     minute: '2-digit',
//                     second: '2-digit',
//                     meridiem: false,
//                     formatMatcher: 'basic'
//                 });

//                 $.ajax({
//                     url: "/full-calender/action",
//                     type: "POST",
//                     data: {
//                         title: title,
//                         start: start,
//                         end: end,
//                         type: 'add'
//                     },
//                     success: function(data) {
//                         calendar.refetchEvents();
//                         alert("Event Created Successfully");
//                     }
//                 });
//             }
//         },
//         eventResize: function(event) {
//             var start = FullCalendar.formatDate(event.event.start, 'Y-MM-DD HH:mm:ss');
//             var end = FullCalendar.formatDate(event.event.end, 'Y-MM-DD HH:mm:ss');
//             var title = event.event.title;
//             var id = event.event.id;
//             $.ajax({
//                 url: "/full-calender/action",
//                 type: "POST",
//                 data: {
//                     title: title,
//                     start: start,
//                     end: end,
//                     id: id,
//                     type: 'update'
//                 },
//                 success: function(response) {
//                     calendar.refetchEvents();
//                     alert("Event Updated Successfully");
//                 }
//             });
//         },
//         eventDrop: function(event) {
//             var start = FullCalendar.formatDate(event.event.start, 'Y-MM-DD HH:mm:ss');
//             var end = FullCalendar.formatDate(event.event.end, 'Y-MM-DD HH:mm:ss');
//             var title = event.event.title;
//             var id = event.event.id;
//             $.ajax({
//                 url: "/full-calender/action",
//                 type: "POST",
//                 data: {
//                     title: title,
//                     start: start,
//                     end: end,
//                     id: id,
//                     type: 'update'
//                 },
//                 success: function(response) {
//                     calendar.refetchEvents();
//                     alert("Event Updated Successfully");
//                 }
//             });
//         },
//         eventClick: function(event) {
//             if (confirm("Are you sure you want to remove it?")) {
//                 var id = event.event.id;
//                 $.ajax({
//                     url: "/full-calender/action",
//                     type: "POST",
//                     data: {
//                         id: id,
//                         type: "delete"
//                     },
//                     success: function(response) {
//                         calendar.refetchEvents();
//                         alert("Event Deleted Successfully");
//                     }
//                 });
//             }
//         }
//     });

//     calendar.render();
// });
