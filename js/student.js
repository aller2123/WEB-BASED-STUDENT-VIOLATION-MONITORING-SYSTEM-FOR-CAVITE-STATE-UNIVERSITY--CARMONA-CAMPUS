
$(document).ready(function() {
    $('.form-control').focus(function() {
        $(this).siblings('label').addClass('label-hidden');
    });

    $('.form-control').blur(function() {
        if (!$(this).val()) {
            $(this).siblings('label').removeClass('label-hidden');
        }
    });

    // Handle the "See Your Information" form submission
    $('#student-info-form').submit(function(e) {
        e.preventDefault();
        var student_no = $('#student_no').val();

        // Send AJAX request to get student information
        $.ajax({
            url: 'get_student_info.php',
            type: 'POST',
            data: { student_no: student_no },
            dataType: 'json',
            success: function(response) {
if (response.error) {
$('#student-info').html('<p>' + response.error + '</p>');
} else {
var studentInfo = '<p><strong>Student No:</strong> ' + response.student_no + '</p>';
studentInfo += '<p><strong>First Name:</strong> ' + response.first_name + '</p>';
studentInfo += '<p><strong>Surname:</strong> ' + response.surname + '</p>';
studentInfo += '<p><strong>Middle Name:</strong> ' + response.middle_name + '</p>';
studentInfo += '<p><strong>Gender:</strong> ' + response.gender + '</p>';
studentInfo += '<p><strong>Address:</strong> ' + response.address + '</p>';
studentInfo += '<p><strong>Age:</strong> ' + response.age + '</p>';
studentInfo += '<p><strong>Status:</strong> ' + response.status + '</p>';
studentInfo += '<p><strong>Program:</strong> ' + (response.program_name ? response.program_name : 'N/A') + '</p>';
studentInfo += '<p><strong>Total Attendance:</strong> ' + (response.total_attendance ? response.total_attendance : 'N/A') + '</p>';
studentInfo += '<p><strong>Event Titles:</strong> ' + (response.event_titles ? response.event_titles : 'N/A') + '</p>';

// Display violation details
if (response.violations.length > 0) {
    studentInfo += '<p><strong>Violations:</strong></p>';
    studentInfo += '<ul>';
    response.violations.forEach(function(violation) {
        studentInfo += '<li>';
        studentInfo += '<p><strong>Violation Type:</strong> ' + violation.type_of_violation + '</p>';
        studentInfo += '<p><strong>Violation Details:</strong> ' + violation.full_info + '</p>';
        studentInfo += '</li>';
    });
    studentInfo += '</ul>';
} else {
    studentInfo += '<p><strong>Violations:</strong> N/A</p>';
}

$('#student-info').html(studentInfo);
}

// Show the modal
$('#studentInfoModal').modal('show');
},


            error: function() {
                $('#student-info').html('<p>An error occurred while fetching student information.</p>');
                $('#studentInfoModal').modal('show');
            }
        });
    });
});