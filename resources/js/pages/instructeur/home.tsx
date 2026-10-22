import RolHome from '@/components/rol-home';

// Homepage voor instructeurs. Laravel toont deze pagina via de route 'instructeur.home'.
// Elke kaart opent hetzelfde overzicht met een andere weergave (dag, week of maand).
export default function InstructeurHome() {
    return (
        <RolHome
            titel="Instructeur"
            uitleg="Hier zie je welke lessen je moet geven."
            onderdelen={[
                {
                    titel: 'Lessen van vandaag',
                    uitleg: 'Bekijk je dagoverzicht.',
                    href: '/instructeur/lessen?weergave=dag',
                },
                {
                    titel: 'Weekoverzicht',
                    uitleg: 'Bekijk je lessen van deze week.',
                    href: '/instructeur/lessen?weergave=week',
                },
                {
                    titel: 'Maandoverzicht',
                    uitleg: 'Bekijk je lessen van deze maand.',
                    href: '/instructeur/lessen?weergave=maand',
                },
            ]}
        />
    );
}

InstructeurHome.layout = {
    breadcrumbs: [{ title: 'Instructeur', href: '/instructeur' }],
};