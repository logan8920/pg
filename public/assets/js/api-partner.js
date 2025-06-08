(function () {
    document.addEventListener("DOMContentLoaded", function () {
        const phoneNo = window?.Laravel?.phone;
        const apiPartner = window?.Laravel?.api_partner;
        const generateOtpUrl = window?.Laravel?.routes?.generateOtp;
        const ipWhiteListUrl = window?.Laravel?.routes?.ipWhitelist;
        const otpModel = document.querySelector("#otpModel");
        const resendAgain = document.querySelector("#resendAgain");
        const maskedNumber = document.querySelector("#maskedNumber");

        let verifyCallback = undefined;
        let selectedPartner = undefined;

        function OTPInput() {
            const inputs = document.querySelectorAll("#otp > input");
            inputs.forEach((input, i) => {
                input.addEventListener("input", function () {
                    this.value = this.value.slice(0, 1);
                    if (this.value !== "" && i < inputs.length - 1) {
                        inputs[i + 1].focus();
                    }
                });
                input.addEventListener("keydown", function (event) {
                    if (event.key === "Backspace") {
                        this.value = "";
                        if (i > 0) inputs[i - 1].focus();
                    }
                });
            });
        }

        function handleValidateClick() {
            document
                .getElementById("validateBtn")
                ?.addEventListener("click", async function () {
                    let otp = "";
                    document
                        .querySelectorAll("#otp > input")
                        .forEach((input) => (otp += input.value));

                    if (otp.length < 6) {
                        document.querySelectorAll("#otp > input").forEach((input) => {
                            input.classList.toggle("error", input.value === "");
                        });
                        return;
                    }

                    toggleLoader();

                    const otpVerfyPayload = new FormData();
                    if (verifyCallback)
                        otpVerfyPayload.append("verifyCallback", verifyCallback);
                    otpVerfyPayload.append("id", selectedPartner?.id);
                    otpVerfyPayload.append("otp", otp);
                    otpVerfyPayload.append("phone", maskedNumber.textContent);

                    const verifyUrl = this.dataset.action;
                    if (!verifyUrl) {
                        toastr["warning"]("Otp Verify Url is missing!!");
                        return;
                    }

                    try {
                        const otpVerifyResponse = await makeHttpRequest(
                            verifyUrl,
                            "POST",
                            otpVerfyPayload,
                            true
                        );
                        if (otpVerifyResponse.success) {
                            toastr.success(otpVerifyResponse.success);
                            
                            otpVerifyResponse.callback && bindSendNotification(selectedPartner?.id);
                            
                            if (otpVerifyResponse.tableReqload) table.ajax.reload();
                            $(otpModel).modal("hide");
                            $(otpModel).find("input").val("");
                        } else {
                            const msg =
                                otpVerifyResponse?.error ||
                                otpVerifyResponse?.message ||
                                "Something went wrong :(";
                            toastr["error"](msg);
                        }
                    } catch (error) {
                        toastr["error"](error);
                    }

                    toggleLoader();
                });
        }

        function bindResendOtp() {
            resendAgain?.addEventListener("click", async function () {
                toggleLoader();
                const otpPayload = new FormData();
                if (phoneNo == '') {
                    toastr["warning"]("Phone not updated in database!");
                    return;
                }
                otpPayload.append("phone", phoneNo);
                otpPayload.append("otp_for", "key_gen");

                const otpUrl = generateOtpUrl;

                try {
                    const otpRes = await makeHttpRequest(
                        otpUrl,
                        "POST",
                        otpPayload,
                        true
                    );
                    if (otpRes?.success) {
                        maskedNumber.textContent = phoneNo;
                        verifyCallback = "createKeyGenDetails";
                        toastr["success"](otpRes?.success);
                    } else {
                        const msg =
                            otpRes?.error || otpRes?.message || "Something went wrong :(";
                        toastr["error"](msg);
                    }
                } catch (error) {
                    alert(error);
                    toastr["error"](error);
                }

                toggleLoader();
            });
        }

        async function credentialsModalShow(target, e, id, type) {

            e.preventDefault();
            const data = tableData[id];
            const $otp = $(otpModel);

            const message = data?.api_credentials
                ? `Want to regenerate API credentials for ${data?.firmname.toUpperCase()}?`
                : `Want to generate API credentials for ${data?.firmname.toUpperCase()}?`;

            const userResponse = await confirmation({
                redirectMessage: "Are You Sure",
                redirectConfirmation: message,
            });

            if (!userResponse) return;

            if (phoneNo == '') {
                toastr["warning"]("Phone not updated in database!");
                return;
            }

            toggleLoader();
            const otpPayload = new FormData();
            selectedPartner = data;
            otpPayload.append("phone", phoneNo);
            otpPayload.append("otp_for", "key_gen");

            try {
                const otpRes = await makeHttpRequest(
                    generateOtpUrl,
                    "POST",
                    otpPayload,
                    true
                );
                if (otpRes?.success) {
                    maskedNumber.textContent = phoneNo;
                    verifyCallback = "createKeyGenDetails";
                    $otp.modal("show");
                    toastr.success(otpRes?.success);
                } else {
                    const msg =
                        otpRes?.error || otpRes?.message || "Something went wrong :(";
                    toastr["error"](msg);
                }
            } catch (error) {
                toastr["error"](error);
            }

            toggleLoader();

        }

        async function bindSendNotification(id) {

            const pData = tableData[id];

            const result = await Swal.fire({
                title: `Key Generated Successfully${pData?.ipaddress ? " - Update IP?" : ""
                    }`,
                text: "Enter IP to whitelist",
                input: "text",
                icon: "success",
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: "Whitelist IP & Send Mail",
                denyButtonText: "Whitelist IP Only",
                preConfirm: async (ip) => ip,
                showLoaderOnConfirm: true,
            });

            if (result.isConfirmed || result.isDenied) {
                try {
                    const data = new FormData();
                    data.append("id", pData.id);
                    data.append("ip", result.value);
                    data.append("send_mail", result.isConfirmed ? "1" : "0");

                    const response = await makeHttpRequest(
                        ipWhiteListUrl,
                        "POST",
                        data,
                        true
                    );
                    if (response.success) {
                        toastr.success(response.success);
                        if (response.tableReqload) table.ajax.reload();
                    } else {
                        toastr["error"](
                            response?.error ||
                            response?.message ||
                            "Something went wrong :("
                        );
                    }
                } catch (error) {
                    Swal.fire("Error", `Request failed: ${error}`, "error");
                }
            }
        }

        // Initialization
        OTPInput();
        handleValidateClick();
        bindResendOtp();
        // bindCredentialsModalShow();

        document.addEventListener('click', async function (e) {
            const target = e.target.closest('[data-credentials]');
            if (target) {
                const id = target.getAttribute('data-id');
                const type = target.getAttribute('data-type');
                await credentialsModalShow(target, e, id, type);
            }

            const notification = e.target.closest('[data-send-notification]');
            if (notification) {
                const id = notification.getAttribute('data-id');
                const type = notification.getAttribute('data-type');
                (apiPartner === 0) && await bindSendNotification(id);
            }
        });


    });
})();
