$(function () {
    $(document).on("submit", ".any-form", async function (e) {
        e.preventDefault();
        if (!$(this).valid()) return false;
        const form = this;
        const $callback = $(this).attr('callbackfn');
        const $postCallback = $(this).attr('postcallbackfn');
        console.log($(this).attr('postcallbackfn'));
        if ($callback && $callback.attr('callbackfn')) {
            const Fn = $callback.attr('callbackfn');
            const callbackRes = await window[Fn]();
            if (!callbackRes) {
                typeof (callbackErrorMessage) !== 'undefined' && toastr.error(callbackErrorMessage);
                return false;
            }
        }

        const button = this.querySelector('[type=submit]');
        const forms = this;
        const buttonText = button.innerHTML;
        startLoadings(button);
        await delay(1000);
        const formData = new FormData(form);
        let headers = false;
        if (!formData.has('_token')) {
            headers = true;
        }
        form && $('select,input,textarea', form).attr('disabled', true);
        var url = this.action;

        try {
            const res = await makeHttpRequest(url, (form?.method || 'post'), formData, headers);
            if (res.success) {

                if ($postCallback) {
                    const Pfn = $postCallback;
                    console.log(Pfn);
                    const callbackRes = await window[Pfn]();
                    if (!callbackRes) {
                        typeof (callbackErrorMessage) !== 'undefined' && toastr.error(callbackErrorMessage);
                        return false;
                    }
                }

                stopLoadings(button, buttonText)

                if (res.sweetAlert) {
                    try {
                        Swal.fire({
                            title: "Successful",
                            text: res.success,
                            icon: "success",
                            allowOutsideClick: false
                        }).then(async (result) => {
                            if (result.isConfirmed) {
                                res.redirectConfirmation && await confirmation(res);
                                window.scrollTo({
                                    top: 10,
                                });
                                if (res?.redirect) {
                                    toastr.success('Redirecting...');
                                    setTimeout(() => { window.location = res.redirect }, 1500);
                                }
                            }
                        });

                        if (res?.tableReqload) {
                            table?.ajax && table.ajax.reload();
                        }
                        try {
                            if (typeof closeBtn !== undefined) closeBtn.click();
                        } catch (error) {

                        }


                    } catch (error) {
                        alert(error)
                    }
                } else {
                    toastr.success(res.success);
                }

                if (res.reloadReq) {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000)
                }
                forms.reset();

                
            } else if (res.message) {
                toastr.error(res.message);
            }

            if (res.error) {
                toastr.error(res.error);
            }

            if (res.validationError) {
                Object.keys(res.validationError).forEach(message => {
                    toastr.error(res.validationError[message]);
                })

            }

            if (res.validationErrorToastr) {
                Object.keys(res.validationErrorToastr).forEach(message => {
                    toastr.error(res.validationErrorToastr[message]);
                });
            }

            if (!res.success && res.redirect) {
                toastr.success('Redirecting...');
                setTimeout(() => { window.location = res.redirect }, 1500);
            }

            if (res.csrfToken) $('input[type=hidden]').val(res.csrfToken);
        } catch (error) {
            toastr.error(error);
        }
        form && $('select,input,textarea', form).removeAttr('disabled');
        stopLoadings(button, buttonText);
    });
});