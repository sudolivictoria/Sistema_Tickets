<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public $count = 0;
    
    public function increment() {
        $this->count++;
    }
}; ?>

<div class="p-8">
    <h1>Test Livewire</h1>
    <p>Count: {{ $count }}</p>
    <button wire:click="increment" style="padding: 10px 20px; background: blue; color: white; border: none; cursor: pointer;">
        Click me
    </button>
</div>
