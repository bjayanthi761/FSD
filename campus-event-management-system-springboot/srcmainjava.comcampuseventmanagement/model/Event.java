package com.campus.eventmanagement.model;

import jakarta.persistence.*;

@Entity
public class Event {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    private String name;
    private String department;
    private String type;
    private String date;
    private String description;

    // Getters & Setters
}
