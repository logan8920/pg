<div class="modal" id="otpModel" data-backdrop="static" tabindex="-1" aria-labelledby="otpModelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="container height-100 d-flex justify-content-center align-items-center px-5 py-2">
                <div class="position-relative">
                    <div class="border-0 card p-2 pb-5 pt-5 text-center">
                        <h6>Please enter the one time password <br> to generate Api Credentials.</h6>
                        <div> <span>A code has been sent to</span> <small id="maskedNumber">*******9897</small> </div>
                        <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2">
                            <input class="m-2 text-center form-control rounded num w-100" autocomplete="off" type="number"
                                id="first" maxlength="1" />
                            <input class="m-2 text-center form-control rounded num w-100" autocomplete="off" type="number"
                                id="second" maxlength="1" />
                            <input class="m-2 text-center form-control rounded num w-100" autocomplete="off" type="number"
                                id="third" maxlength="1" />
                            <input class="m-2 text-center form-control rounded num w-100" autocomplete="off" type="number"
                                id="fourth" maxlength="1" />
                            <input class="m-2 text-center form-control rounded num w-100" autocomplete="off" type="number"
                                id="fifth" maxlength="1" />
                            <input class="m-2 text-center form-control rounded num w-100" autocomplete="off" type="number"
                                id="sixth" maxlength="1" />
                        </div>
                        <div class="mt-4">
                            <button id="validateBtn" data-action="{{ route('verify.otp') }}"
                                class="btn btn-danger px-4 validate">Validate</button>
                            <br>
                            <a href="javascript:;" id="resendAgain" class="mt-2" style="font-size: 12px">Resend</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

