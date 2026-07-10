/* Minimal helper for backend calls */
(function(){
  const api = {
    base: 'backend',
    async me(){
      const r = await fetch(`${this.base}/me.php`, { headers: { 'Accept':'application/json' } });
      const j = await r.json().catch(()=>({user:null}));
      return j.user || null;
    },
    async login(email, password){
      const r = await fetch(`${this.base}/auth_login.php`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({ email, password })
      });
      return r.json();
    },
    async signup(name, email, password){
      const r = await fetch(`${this.base}/auth_signup.php`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({ name, email, password })
      });
      return r.json();
    },
    async logout(){
      const r = await fetch(`${this.base}/auth_logout.php`);
      return r.json();
    },
    async saveNutrition(payload){
      const r = await fetch(`${this.base}/nutrition_save.php`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify(payload)
      });
      return r.json();
    },
    async saveWorkout(payload){
      const r = await fetch(`${this.base}/workout_save.php`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify(payload)
      });
      return r.json();
    },
    async saveTasks(payload){
      const r = await fetch(`${this.base}/tasks_save.php`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify(payload)
      });
      return r.json();
    }
  };

  window.BackendAPI = api;
})();



