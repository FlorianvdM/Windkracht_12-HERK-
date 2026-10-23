import { Link, usePage } from '@inertiajs/react';
import {
    CalendarDays,
    CalendarPlus,
    CalendarX,
    LayoutGrid,
    ListChecks,
    Package,
    ScrollText,
    Users,
    Wallet,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem, Role } from '@/types';

// Welke links ziet welke rol? De eigenaar heeft toegang tot alle pagina's,
// dus die ziet ook de homepages van de klant en de instructeur.
const menuPerRol: Record<Role, NavItem[]> = {
    klant: [
        { title: 'Home', href: '/klant', icon: LayoutGrid },
        { title: 'Reserveren', href: '/klant/reserveren', icon: CalendarPlus },
        {
            title: 'Mijn reserveringen',
            href: '/klant/reserveringen',
            icon: ListChecks,
        },
    ],
    // NIEUW: de instructeur ziet zijn lessen direct in het menu.
    instructeur: [
        { title: 'Home', href: '/instructeur', icon: LayoutGrid },
        { title: 'Mijn lessen', href: '/instructeur/lessen', icon: CalendarDays },
    ],
    // NIEUW: alle pagina's van de eigenaar staan nu in het menu.
    eigenaar: [
        { title: 'Home', href: '/eigenaar', icon: LayoutGrid },
        { title: 'Reserveringen', href: '/eigenaar/reserveringen', icon: ListChecks },
        {
            title: 'Openstaande betalingen',
            href: '/eigenaar/reserveringen?filter=onbetaald',
            icon: Wallet,
        },
        { title: 'Annuleringen', href: '/eigenaar/annuleringen', icon: CalendarX },
        { title: 'Materiaal', href: '/eigenaar/materiaal', icon: Package },
        { title: 'Logboek', href: '/eigenaar/logboek', icon: ScrollText },
        { title: 'Klantpagina', href: '/klant', icon: Users },
        { title: 'Instructeurpagina', href: '/instructeur', icon: CalendarDays },
    ],
};

export function AppSidebar() {
    // De ingelogde gebruiker (met rol) komt van Laravel.
    const { auth } = usePage().props;
    const rol = auth.user.role;

    // 'klant' wordt 'Klant' (eerste letter hoofdletter).
    const rolNaam = rol.charAt(0).toUpperCase() + rol.slice(1);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            {/* /dashboard stuurt door naar de homepage van de rol */}
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={menuPerRol[rol]} />
            </SidebarContent>

            <SidebarFooter>
                {/* Casus: op elke pagina staat wie is ingelogd en met welke rol. */}
                <p className="px-2 text-xs text-muted-foreground group-data-[collapsible=icon]:hidden">
                    Ingelogd als{' '}
                    <span className="font-medium text-foreground">
                        {auth.user.name}
                    </span>{' '}
                    ({rolNaam})
                </p>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}