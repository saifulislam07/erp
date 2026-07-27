<style>
    body.login-page {
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        min-height: 100vh;
    }

    body.login-page .login-box {
        width: 400px;
        animation: erp-login-fade-in .4s ease-out;
    }

    body.login-page .login-logo {
        margin-bottom: 1.5rem;
    }

    body.login-page .login-logo a {
        color: #fff;
        font-size: 1.9rem;
        font-weight: 700;
        letter-spacing: .5px;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }

    body.login-page .login-logo a::before {
        content: "\f542";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        color: #4dd0e1;
    }

    body.login-page .card {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 45px rgba(0, 0, 0, .35);
    }

    body.login-page .card-header {
        background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);
        border-bottom: 0;
        padding: 1.5rem 1.5rem 1.25rem;
    }

    body.login-page .card-header .card-title {
        color: #fff;
        font-weight: 600;
        font-size: 1.15rem;
        margin: 0;
    }

    body.login-page .login-card-body {
        padding: 1.75rem 1.75rem 1.5rem;
        background: #fff;
    }

    body.login-page .input-group-text {
        background: #f4f6f9 !important;
        border: 1px solid #ced4da !important;
        color: #6c757d;
    }

    body.login-page .form-control {
        border: 1px solid #ced4da !important;
    }

    body.login-page .form-control:focus {
        box-shadow: none;
        border-color: #71b280 !important;
    }

    body.login-page .input-group:focus-within .input-group-text {
        border-color: #71b280 !important;
    }

    body.login-page .btn-primary {
        background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);
        border: 0;
        font-weight: 600;
        letter-spacing: .3px;
        transition: filter .15s ease-in-out;
    }

    body.login-page .btn-primary:hover,
    body.login-page .btn-primary:focus {
        filter: brightness(1.1);
    }

    body.login-page .card-footer {
        background: #fff;
        border-top: 1px solid #eef0f3;
        text-align: center;
    }

    body.login-page .alert {
        border-radius: 8px;
    }

    @keyframes erp-login-fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
