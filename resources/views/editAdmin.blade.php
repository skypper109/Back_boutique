
<style>
.edit-container {
    background: #fff;
    padding: 2.2rem 2rem;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(60, 72, 98, 0.13);
    min-width: 320px;
    max-width: 400px;
    width: 100%;
    margin: 40px auto 0 auto;
}
.edit-container h2 {
    color: #4f46e5;
    margin-bottom: 1.1rem;
    text-align: center;
}
.form-group {
    margin-bottom: 1.1rem;
}
label {
    display: block;
    color: #334155;
    font-weight: 500;
    margin-bottom: 4px;
}
input[type="text"], input[type="email"] {
    width: 100%;
    padding: 10px 12px;
    border: 1.3px solid #d1d5db;
    border-radius: 7px;
    font-size: 1rem;
    outline: none;
    transition: border 0.18s;
    background: #f8fafc;
}
input[type="text"]:focus, input[type="email"]:focus {
    border: 1.5px solid #4f46e5;
    background: #f5f3ff;
}
.edit-btn {
    width: 100%;
    background: linear-gradient(90deg, #6366f1 0%, #818cf8 100%);
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 1.09rem;
    font-weight: bold;
    letter-spacing: 0.04em;
    cursor: pointer;
    margin-top: 8px;
    box-shadow: 0 3px 14px rgba(99,102,241,0.09);
    transition: background 0.18s, box-shadow 0.18s;
}
.edit-btn:hover {
    background: linear-gradient(90deg, #4338ca 0%, #6366f1 100%);
    box-shadow: 0 6px 24px rgba(99,102,241,0.18);
}
@media (max-width: 500px) {
    .edit-container {
        min-width: 95vw;
        padding: 1.2rem 0.6rem;
    }
}
</style>
<div class="edit-container">
    <h2>Modifier l’Administrateur</h2>
    @if($errors->any())
        <div style="color:#ef4444; margin-bottom:1rem;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('admin.update', $admin->id) }}">
        @csrf
        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" name="nom" value="{{ old('nom', $admin->nom) }}" required>
        </div>
        <div class="form-group">
            <label for="email">Adresse email</label>
            <input type="email" name="email" value="{{ old('email', $admin->email) }}" required>
        </div>
        <button class="edit-btn" type="submit">Mettre à jour</button>
    </form>
</div>
