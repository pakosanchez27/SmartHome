<x-layouts.app
    title="Mi hogar"
    :breadcrumbs="[['label' => 'Mi hogar', 'url' => route('household.index')], ['label' => 'Integrantes']]"
    :demo="false"
>
    @php
        /** @var $members \Illuminate\Support\Collection<\App\Models\User> $members */
        /** @var $invitations \Illuminate\Support\Collection<\App\Models\HouseholdInvitation> $invitations */
        $tabs = [
            ['label' => 'Integrantes', 'active' => true],
            ['label' => 'Mi perfil', 'href' => route('profile.edit')],
            ['label' => 'Permisos y privacidad'], // EP03-05/07
            ['label' => 'Actividad'],             // EP03-08/09
        ];

        $abrirModal = $errors->hasAny(['email', 'role', 'message']);
    @endphp

    <x-page-header
        eyebrow="Mi hogar"
        title="Integrantes"
        description="Organicen su hogar, con un lugar y un permiso por cada persona."
    >
        <x-slot:actions>
            @if ($household !== null && $viewerRole === 'admin')
                <x-button icon="user-plus" x-on:click="$dispatch('open-modal', 'invitar-integrante')">
                    Invitar integrante
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <x-banner tone="success">{{ session('status') }}</x-banner>
    @endif
    @if ($errors->has('invitaciones'))
        <x-banner tone="danger">{{ $errors->first('invitaciones') }}</x-banner>
    @endif

    <x-tabs :items="$tabs" label="Vistas de Mi hogar" />

    @if ($household === null)
        <x-card spacing="p-8">
            <x-empty-state
                icon="users"
                title="Todavía no perteneces a un hogar"
                description="Si recibiste un enlace de invitación por correo, ábrelo desde tu bandeja para entrar al hogar. Sin invitación, pide a quien administra tu hogar que te invite."
            />
        </x-card>
    @else
        <x-card spacing="p-5">
            <div class="space-y-3">
                <h2 class="text-base font-semibold text-ink">Personas que hacen hogar</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge tone="primary">{{ $memberCount }} {{ $memberCount === 1 ? 'integrante activo' : 'integrantes activos' }}</x-badge>
                    @if ($pendingCount > 0)
                        <x-badge tone="warning">{{ $pendingCount }} {{ $pendingCount === 1 ? 'invitación pendiente' : 'invitaciones pendientes' }}</x-badge>
                    @endif
                </div>
            </div>

            <ul class="divide-y divide-border">
                @foreach ($members as $member)
                    @php
                        $role = $member->pivot->role;
                        $roleTone = $role === 'admin' ? 'primary' : ($role === 'member' ? 'info' : 'neutral');
                        $initials = collect(explode(' ', trim($member->name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    @endphp
                    <li class="flex min-h-11 flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-info-soft text-sm font-semibold text-info">{{ strtoupper($initials) }}</span>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink">
                                    {{ $member->name }}@if ($member->id === \Illuminate\Support\Facades\Auth::id()) <span class="font-normal text-muted">· Tú</span>@endif
                                </p>
                                <p class="truncate text-[13px] text-muted">{{ $member->email }}</p>
                                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                    <x-badge :tone="$roleTone">{{ \App\Models\HouseholdInvitation::roleLabelFor($role) }}</x-badge>
                                    <span class="text-[12px] text-muted">Se incorporó el {{ $member->pivot->created_at?->translatedFormat('j M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <x-badge tone="success">Activo</x-badge>
                        </div>
                    </li>
                @endforeach
            </ul>

            <p class="text-[12px] leading-relaxed text-muted">
                Cambiar roles, desactivar o retirar integrantes llega con el módulo de permisos (EP03-05/06).
            </p>
        </x-card>

        <x-card spacing="p-5">
            <h2 class="text-base font-semibold text-ink">Invitaciones</h2>

            @if ($invitations->isEmpty())
                <p class="text-[13px] text-muted">Aún no has invitado a nadie a este hogar.</p>
            @else
                <ul class="divide-y divide-border">
                    @foreach ($invitations as $invitation)
                        @php
                            $pendiente = $invitation->status === \App\Models\HouseholdInvitation::STATUS_PENDING;
                            $vencida = $pendiente && $invitation->expires_at->isPast();
                            $inicial = mb_strtoupper(mb_substr($invitation->email, 0, 1));
                        @endphp
                        <li class="flex min-h-11 flex-col gap-3 py-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-warning-soft text-sm font-semibold text-warning">{{ $inicial }}</span>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $invitation->email }}</p>
                                    <p class="truncate text-[13px] text-muted">{{ $invitation->roleLabel() }} · Invitada por {{ $invitation->invitedBy->name }}</p>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                        @if ($pendiente && ! $vencida)
                                            <x-badge tone="warning">Pendiente</x-badge>
                                            <span class="text-[12px] text-muted">Enviada el {{ $invitation->created_at->translatedFormat('j M') }} · Vence el {{ $invitation->expires_at->translatedFormat('j M Y') }}</span>
                                        @elseif ($vencida)
                                            <x-badge tone="warning">Vencida</x-badge>
                                            <span class="text-[12px] text-muted">Venció el {{ $invitation->expires_at->translatedFormat('j M Y') }}</span>
                                        @elseif ($invitation->status === \App\Models\HouseholdInvitation::STATUS_ACCEPTED)
                                            <x-badge tone="primary">Aceptada el {{ $invitation->accepted_at?->translatedFormat('j M Y') }}</x-badge>
                                        @else
                                            <x-badge tone="neutral">{{ $invitation->status === \App\Models\HouseholdInvitation::STATUS_REJECTED ? 'Rechazada' : 'Cancelada' }}</x-badge>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-[12px] leading-relaxed text-muted">Todavía no tiene acceso. Podrá ver solo información compartida cuando acepte.</p>
                                </div>
                            </div>

                            @if ($pendiente && $viewerRole === 'admin')
                                <div class="flex shrink-0 flex-wrap gap-2">
                                    <form method="POST" action="{{ route('household.invitations.resend', $invitation) }}"
                                          x-data x-on:submit="if (!confirm('Reenviar la invitación generará un enlace nuevo; el anterior dejará de funcionar.')) event.preventDefault();"
                                    >
                                        @csrf
                                        <x-button variant="outline" type="submit">Reenviar</x-button>
                                    </form>
                                    <form method="POST" action="{{ route('household.invitations.cancel', $invitation) }}"
                                          x-data x-on:submit="if (!confirm('¿Cancelar la invitación? El enlace dejará de funcionar.')) event.preventDefault();"
                                    >
                                        @csrf
                                        <x-button variant="outline" type="submit">Cancelar invitación</x-button>
                                    </form>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    @endif

    <x-banner tone="info" title="Compartir hogar no significa compartir todo">
        Las cuentas personales son privadas, incluso para la administradora. Cada titular decide qué compartir.
    </x-banner>

    @if ($household !== null && $viewerRole === 'admin')
        <x-modal name="invitar-integrante" title="Invitar a tu hogar" description="Dale acceso a lo compartido. Sus cuentas personales seguirán siendo privadas.">
            <form method="POST" action="{{ route('household.invitations.store') }}"
                  @if ($abrirModal) x-init="$dispatch('open-modal', 'invitar-integrante')" @endif
            >
                @csrf
                <div class="space-y-5">
                    <x-input label="Correo electrónico" name="email" type="email" required maxlength="255" placeholder="nombre@correo.mx" hint="El enlace será válido durante 7 días." />
                    <x-select label="Rol en el hogar" name="role" required hint="Puede ver lo compartido; no editará ni invitará.">
                        <option value="viewer" @selected(old('role', 'viewer') === 'viewer')">Consulta</option>
                        <option value="member" @selected(old('role') === 'member')">Integrante</option>
                    </x-select>
                    <x-textarea label="Mensaje (opcional)" name="message" maxlength="300" placeholder="¡Hola! Te invitamos a organizar nuestro hogar juntos." hint="Hasta 300 caracteres." />
                </div>

                <x-banner tone="info" title="Antes de enviar">
                    Quien reciba la invitación verá listas, recetas, planes y registros compartidos. No verá cuentas ni registros personales.
                </x-banner>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-button variant="outline" type="button" x-on:click="$dispatch('close-modal', 'invitar-integrante')">Volver sin enviar</x-button>
                    <x-button type="submit">Enviar invitación</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</x-layouts.app>
