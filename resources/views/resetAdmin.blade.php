<style>
.reset-container {
    background: #fff;
    padding: 2.2rem 2rem;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(60, 72, 98, 0.13);
    min-width: 320px;
    max-width: 400px;
    width: 100%;
    margin: 40px auto 0 auto;
    text-align: center;
}
.reset-container h2 {
    color: #4f46e5;
    margin-bottom: 1.1rem;
}
.reset-btn {
    width: 100%;
    background: linear-gradient(90deg, #fb923c 0%, #fbbf24 100%);
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 1.09rem;
    font-weight: bold;
    letter-spacing: 0.04em;
    cursor: pointer;
    margin-top: 8px;
    box-shadow: 0 3px 14px rgba(251,146,60,0.09);
    transition: background 0.18s, box-shadow 0.18s;
}
.reset-btn:hover {
    background: linear-gradient(90deg, #fbbf24 0%, #fb923c 100%);
    box-shadow: 0 6px 24px rgba(251,146,60,0.15);
}
@media (max-width: 500px) {
    .reset-container {
        min-width: 95vw;
        padding: 1.2rem 0.6rem;
    }
}
</style>
<div class="reset-container">
    <h2>Réinitialiser le mot de passe</h2>
    <p>Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet administrateur&nbsp;?</p>
    <form method="POST" action="{{ route('admin.reset', $admin->id) }}">
        @csrf
        <button class="reset-btn" type="submit"
            onclick="return confirm('Confirmer la réinitialisation du compte admin ?')">
            Réinitialiser le compte
        </button>
    </form>
    @if(session('success'))
        <div style="color:green; margin-top:15px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="color:#ef4444; margin-top:12px;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
</div>
