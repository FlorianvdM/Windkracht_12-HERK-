import RolHome from '@/components/rol-home';

// Homepage voor instructeurs. Laravel toont deze pagina via de route 'instructeur.home'.
export default function InstructeurHome() {
    return (
        <RolHome
            titel="Instructeur"
            uitleg="Hier zie je welke lessen je moet geven."
            onderdelen={[
                {
                    titel: 'Mijn lessen',
                    uitleg: 'Bekijk je lessen van vandaag en de komende dagen.',
                },
            ]}
        />
    );
}

InstructeurHome.layout = {
    breadcrumbs: [{ title: 'Instructeur', href: '/instructeur' }],
};
