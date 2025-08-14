function SwalSuccess(message = 'Success!') {
    return Swal.fire({
        title: message,
        icon: 'success',
    })
}

function SwalError(message = 'Error!') {
    return Swal.fire({
        title: message,
        icon: 'error',
    });
}

function SwalConfirm(message = 'Are you sure want to proceed?', title = 'Confirmation!') {
    return Swal.fire({
        
    });
}

function SwalConfirmDelete(message) {
    return Swal.fire({
        title: message || "Are you sure want to delete this data?",
         icon: "question",
         iconColor: "red",
         showCancelButton: true,
    });
}