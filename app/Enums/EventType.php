<?php

namespace App\Enums;

enum EventType: string
{
    case CONFERENCE = 'Conference';
    case WORKSHOP = 'Workshop';
    case SUMMIT = 'Summit';
    case SEMINAR = 'Seminar';
    case WEBINAR = 'Webinar';
    case FESTIVAL = 'Festival';
    case CONCERT = 'Concert';
    case EXHIBITION = 'Exhibition';
    case NETWORKING = 'Networking';
    case HACKATHON = 'Hackathon';
    case RETREAT = 'Retreat';
    case FUNDRAISER = 'Fundraiser';
    case SPORTS = 'Sports & Tournament';
    case PARTY = 'Party & Celebration';
    case OTHER = 'Other';

    public function label(): string
    {
        return $this->value;
    }

    public function info(): string
    {
        return match ($this) {
            self::CONFERENCE => 'Large-scale gatherings focused on a particular industry, field, or topic.',
            self::WORKSHOP => 'Interactive, hands-on sessions designed to teach or practice specific skills.',
            self::SUMMIT => 'High-level meetings bringing together leaders or experts to discuss key issues.',
            self::SEMINAR => 'Presentations or lectures on a specific subject, typically with Q&A.',
            self::WEBINAR => 'Online seminars or presentations conducted over the internet.',
            self::FESTIVAL => 'Celebratory events featuring music, arts, culture, or food.',
            self::CONCERT => 'Live music or performance events for audiences.',
            self::EXHIBITION => 'Showcases for art, products, innovations, or trade.',
            self::NETWORKING => 'Events focused on professional or social connection-building.',
            self::HACKATHON => 'Collaborative sprints where participants build projects or solutions.',
            self::RETREAT => 'Focused gatherings for team building, wellness, or strategic planning.',
            self::FUNDRAISER => 'Events organized to raise funds for a cause or organization.',
            self::SPORTS => 'Sporting competitions, tournaments, races, and athletic events.',
            self::PARTY => 'Celebrations including birthdays, weddings, award nights, and themed parties.',
            self::OTHER => 'Any event that does not fit the above categories.',
        };
    }

    public function getEventSubtypes(EventType $type): array
    {
        return match ($type) {
            EventType::CONFERENCE => ['Tech Conference', 'Academic Conference', 'Industry Conference', 'Medical Conference'],
            EventType::WORKSHOP => ['Training Workshop', 'Creative Workshop', 'Leadership Workshop', 'Coding Bootcamp'],
            EventType::SUMMIT => ['Leadership Summit', 'Startup Summit', 'Government Summit', 'Climate Summit'],
            EventType::SEMINAR => ['Business Seminar', 'Health Seminar', 'Finance Seminar', 'Legal Seminar'],
            EventType::WEBINAR => ['Product Demo', 'Online Training', 'Panel Discussion', 'Livestream Q&A'],
            EventType::FESTIVAL => ['Music Festival', 'Food Festival', 'Arts Festival', 'Cultural Festival'],
            EventType::CONCERT => ['Live Band', 'DJ Night', 'Artiste Night', 'Classical Performance'],
            EventType::EXHIBITION => ['Art Show', 'Trade Show', 'Product Launch', 'Pop-up Shop'],
            EventType::NETWORKING => ['Mixer', 'Speed Networking', 'Industry Meetup', 'Investor Pitch Night'],
            EventType::HACKATHON => ['Tech Hackathon', 'Design Sprint', 'Social Impact Hackathon'],
            EventType::RETREAT => ['Team Building', 'Wellness Retreat', 'Strategy Offsite', 'Leadership Retreat'],
            EventType::FUNDRAISER => ['Charity Gala', 'Awareness Campaign', 'Crowdfunding Event', 'Auction'],
            EventType::SPORTS => ['Tournament', 'Marathon', 'Esports', 'Fitness Challenge'],
            EventType::PARTY => ['Birthday Party', 'Wedding', 'Awards Night', 'Themed Party', 'Baby Shower'],
            EventType::OTHER => ['Community Event', 'Religious Event', 'Town Hall', 'Press Conference'],
        };
    }
}
